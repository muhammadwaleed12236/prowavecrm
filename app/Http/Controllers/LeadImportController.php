<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadImportController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        $agents = $user->isSuperAdmin() ? User::where('role', 'sales_agent')->where('status', 'active')->get() : collect();

        return view('leads.import', compact('agents'));
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="prowave_leads_import_template.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            
            // CSV Header Row
            fputcsv($handle, [
                'Client Name',
                'Company Name',
                'Phone',
                'Email',
                'Country',
                'Service Needed',
                'Source',
                'Estimated Budget',
                'Notes'
            ]);

            // Sample Row 1
            fputcsv($handle, [
                'Alex Turner',
                'Northwind Tech',
                '+1 (555) 432-8765',
                'alex@northwind.com',
                'USA',
                'Custom Web Portal & API',
                'Cold Calling',
                '4500.00',
                'Interested in automating customer order workflows.'
            ]);

            // Sample Row 2
            fputcsv($handle, [
                'Emma Watson',
                'Starlight Apparel',
                '+44 20 7946 0192',
                'emma@starlight.co.uk',
                'UK',
                'E-Commerce Shopify Redesign',
                'Cold Outreach',
                '3200.00',
                'Looking to increase mobile checkout conversion.'
            ]);

            fclose($handle);
        }, 200, $headers);
    }

    public function import(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240', // up to 10MB
            'assignment_mode' => 'nullable|in:specific,round_robin,unassigned',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $file = $request->file('file');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray(null, true, true, true);
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Unable to read file: ' . $e->getMessage()]);
        }

        if (empty($rows) || count($rows) < 2) {
            return back()->withErrors(['file' => 'The uploaded file appears to be empty or has no data rows.']);
        }

        // Header mapping
        $headerRow = array_shift($rows);
        $headerMap = [];

        foreach ($headerRow as $colKey => $headerName) {
            if (!$headerName) continue;
            $clean = strtolower(trim((string)$headerName));
            $clean = str_replace([' ', '_', '-'], '', $clean);

            if (in_array($clean, ['clientname', 'name', 'client', 'contactname', 'fullname'])) {
                $headerMap['client_name'] = $colKey;
            } elseif (in_array($clean, ['company', 'companyname', 'business', 'businessname'])) {
                $headerMap['company_name'] = $colKey;
            } elseif (in_array($clean, ['phone', 'phonenumber', 'mobile', 'cell', 'tel', 'contact'])) {
                $headerMap['phone'] = $colKey;
            } elseif (in_array($clean, ['email', 'emailaddress', 'mail'])) {
                $headerMap['email'] = $colKey;
            } elseif (in_array($clean, ['country', 'region', 'state', 'location'])) {
                $headerMap['country'] = $colKey;
            } elseif (in_array($clean, ['serviceneeded', 'service', 'requirement', 'project', 'serviceinterest'])) {
                $headerMap['service_needed'] = $colKey;
            } elseif (in_array($clean, ['source', 'leadsource', 'channel'])) {
                $headerMap['source'] = $colKey;
            } elseif (in_array($clean, ['budget', 'estimatedbudget', 'amount', 'price'])) {
                $headerMap['budget'] = $colKey;
            } elseif (in_array($clean, ['notes', 'remarks', 'description', 'comments'])) {
                $headerMap['notes'] = $colKey;
            }
        }

        if (!isset($headerMap['client_name']) && !isset($headerMap['phone'])) {
            return back()->withErrors([
                'file' => 'Header columns not recognized. Please make sure your file contains at least "Client Name" (or Name) and "Phone" columns. You can download our sample template.'
            ]);
        }

        $activeAgents = $user->isSuperAdmin() 
            ? User::where('role', 'sales_agent')->where('status', 'active')->get()
            : collect();

        $assignmentMode = $request->input('assignment_mode', 'specific');
        $specificAgentId = $request->input('assigned_to');

        $importedCount = 0;
        $skippedCount = 0;
        $roundRobinIndex = 0;

        foreach ($rows as $row) {
            $clientName = isset($headerMap['client_name']) ? trim((string)($row[$headerMap['client_name']] ?? '')) : '';
            $phone = isset($headerMap['phone']) ? trim((string)($row[$headerMap['phone']] ?? '')) : '';

            // Skip rows without client name or phone
            if (empty($clientName) && empty($phone)) {
                $skippedCount++;
                continue;
            }

            if (empty($clientName)) {
                $clientName = 'Prospect (' . $phone . ')';
            }

            if (empty($phone)) {
                $phone = 'N/A';
            }

            // Determine assignment
            $assignedTo = null;
            if (!$user->isSuperAdmin()) {
                // Sales agent importing is assigned to himself
                $assignedTo = $user->id;
            } else {
                if ($assignmentMode === 'round_robin' && $activeAgents->isNotEmpty()) {
                    $assignedTo = $activeAgents[$roundRobinIndex % $activeAgents->count()]->id;
                    $roundRobinIndex++;
                } elseif ($assignmentMode === 'specific' && $specificAgentId) {
                    $assignedTo = $specificAgentId;
                }
            }

            $companyName = isset($headerMap['company_name']) ? trim((string)($row[$headerMap['company_name']] ?? '')) : null;
            $email = isset($headerMap['email']) ? trim((string)($row[$headerMap['email']] ?? '')) : null;
            $country = isset($headerMap['country']) ? trim((string)($row[$headerMap['country']] ?? '')) : null;
            $serviceNeeded = isset($headerMap['service_needed']) ? trim((string)($row[$headerMap['service_needed']] ?? '')) : null;
            $source = isset($headerMap['source']) && !empty($row[$headerMap['source']]) ? trim((string)$row[$headerMap['source']]) : 'Excel Import';
            $budgetRaw = isset($headerMap['budget']) ? preg_replace('/[^0-9.]/', '', (string)($row[$headerMap['budget']] ?? '')) : null;
            $budget = !empty($budgetRaw) && is_numeric($budgetRaw) ? (float)$budgetRaw : null;
            $notes = isset($headerMap['notes']) ? trim((string)($row[$headerMap['notes']] ?? '')) : null;

            Lead::create([
                'assigned_to' => $assignedTo,
                'created_by' => $user->id,
                'client_name' => $clientName,
                'company_name' => $companyName ?: null,
                'email' => $email ?: null,
                'phone' => $phone,
                'country' => $country ?: null,
                'service_needed' => $serviceNeeded ?: null,
                'source' => $source,
                'status' => 'new',
                'budget' => $budget,
                'notes' => $notes ?: 'Imported via Excel spreadsheet on ' . now()->toFormattedDateString(),
            ]);

            $importedCount++;
        }

        $message = "Successfully imported {$importedCount} leads from spreadsheet!";
        if ($skippedCount > 0) {
            $message .= " ({$skippedCount} empty rows skipped)";
        }

        return redirect()->route('leads.index')->with('success', $message);
    }
}
