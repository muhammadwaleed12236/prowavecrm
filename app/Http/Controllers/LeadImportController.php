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
        $agents = $user->isSuperAdmin() ? User::assignableAgents()->get() : collect();

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
                'Lead Type',
                'Client & Business Name',
                'Phone',
                'Email',
                'Remarks'
            ]);

            // Sample Row 1
            fputcsv($handle, [
                'own',
                'Alex Turner - Northwind Tech',
                '+1 (555) 432-8765',
                'alex@northwind.com',
                'Interested in custom web development'
            ]);

            // Sample Row 2
            fputcsv($handle, [
                'outsource',
                'Emma Watson - Starlight Apparel',
                '+44 20 7946 0192',
                'emma@starlight.co.uk',
                'Looking for Shopify redesign partner'
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
            $clean = str_replace([' ', '_', '-', '/', '&'], '', $clean);

            if (in_array($clean, ['type', 'leadtype', 'category'])) {
                $headerMap['lead_type'] = $colKey;
            } elseif (in_array($clean, ['clientbusinessname', 'clientnamebusinessname', 'clientandbusinessname', 'clientname', 'name', 'client', 'contactname', 'fullname', 'company', 'companyname', 'business', 'businessname'])) {
                $headerMap['client_name'] = $colKey;
            } elseif (in_array($clean, ['phone', 'phonenumber', 'mobile', 'cell', 'tel', 'contact', 'phoneno'])) {
                $headerMap['phone'] = $colKey;
            } elseif (in_array($clean, ['email', 'emailaddress', 'mail'])) {
                $headerMap['email'] = $colKey;
            } elseif (in_array($clean, ['remarks', 'notes', 'description', 'comments', 'remark'])) {
                $headerMap['notes'] = $colKey;
            }
        }

        if (!isset($headerMap['client_name']) && !isset($headerMap['phone'])) {
            return back()->withErrors([
                'file' => 'Header columns not recognized. Please make sure your file contains at least "Client & Business Name" (or Name) and "Phone" columns. You can download our sample template.'
            ]);
        }

        $activeAgents = $user->isSuperAdmin() 
            ? User::assignableAgents()->get()
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

            // Lead type: 'own' or 'outsource'
            $rawType = isset($headerMap['lead_type']) ? strtolower(trim((string)($row[$headerMap['lead_type']] ?? ''))) : 'own';
            $leadType = in_array($rawType, ['outsource', 'outsourced']) ? 'outsource' : 'own';

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

            $email = isset($headerMap['email']) ? trim((string)($row[$headerMap['email']] ?? '')) : null;
            $notes = isset($headerMap['notes']) ? trim((string)($row[$headerMap['notes']] ?? '')) : null;

            Lead::create([
                'assigned_to' => $assignedTo,
                'created_by' => $user->id,
                'lead_type' => $leadType,
                'client_name' => $clientName,
                'email' => $email ?: null,
                'phone' => $phone,
                'source' => 'Excel Import',
                'status' => 'new',
                'notes' => $notes ?: null,
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
