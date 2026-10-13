<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\CallLog;
use App\Models\Meeting;
use App\Models\Deal;
use Carbon\Carbon;

class CallLogController extends Controller
{
    public function store(Request $request, Lead $lead)
    {
        $user = auth()->user();

        // Security: sales agents can only log calls for their assigned leads
        if (!$user->isSuperAdmin() && $lead->assigned_to !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'call_status' => 'required|string|in:answered,no_answer,busy,interested,callback,meeting_scheduled,not_interested,deal_won,wrong_number',
            'remarks' => 'required|string|max:2000',
            'callback_at' => 'nullable|date',
            'call_duration_seconds' => 'nullable|integer|min:0',

            // Optional meeting fields if meeting scheduled
            'meeting_title' => 'nullable|string|max:255',
            'meeting_time' => 'nullable|date',
            'meeting_link' => 'nullable|string|max:255',
            'meeting_agenda' => 'nullable|string',

            // Optional deal won fields if deal closed
            'deal_amount' => 'nullable|numeric|min:0',
            'project_title' => 'nullable|string|max:255',
            'project_scope' => 'nullable|string',
        ]);

        // 1. Create Call Log
        $callbackAt = !empty($validated['callback_at']) ? Carbon::parse($validated['callback_at']) : null;
        $callLog = CallLog::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'call_status' => $validated['call_status'],
            'remarks' => $validated['remarks'],
            'callback_at' => $callbackAt,
            'call_duration_seconds' => $validated['call_duration_seconds'] ?? 0,
        ]);

        // 2. Map Call Status to Lead Status
        $leadStatus = match ($validated['call_status']) {
            'interested' => 'interested',
            'callback' => 'callback',
            'meeting_scheduled' => 'meeting_scheduled',
            'deal_won' => 'deal_won',
            'not_interested' => 'not_interested',
            'no_answer', 'busy' => 'contacted',
            default => 'contacted',
        };

        $lead->update([
            'status' => $leadStatus,
            'last_contacted_at' => now(),
        ]);

        // 3. Handle Meeting Scheduling if applicable
        if ($validated['call_status'] === 'meeting_scheduled') {
            $scheduledTime = !empty($validated['meeting_time']) 
                ? Carbon::parse($validated['meeting_time']) 
                : (!empty($validated['callback_at']) ? Carbon::parse($validated['callback_at']) : now()->addDay()->setTime(15, 0));

            Meeting::create([
                'lead_id' => $lead->id,
                'user_id' => $user->id,
                'title' => !empty($validated['meeting_title']) ? $validated['meeting_title'] : "Sales Meeting with {$lead->client_name}",
                'scheduled_at' => $scheduledTime,
                'meeting_link' => $validated['meeting_link'] ?? null,
                'status' => 'scheduled',
                'agenda' => !empty($validated['meeting_agenda']) ? $validated['meeting_agenda'] : $validated['remarks'],
            ]);
        }

        // 4. Handle Deal Won if applicable
        if ($validated['call_status'] === 'deal_won' && !empty($validated['deal_amount'])) {
            Deal::updateOrCreate(
                ['lead_id' => $lead->id],
                [
                    'user_id' => $user->id,
                    'client_name' => $lead->client_name . ($lead->company_name ? " ({$lead->company_name})" : ''),
                    'project_title' => !empty($validated['project_title']) ? $validated['project_title'] : ($lead->service_needed ?: 'Custom Software Development'),
                    'project_scope' => !empty($validated['project_scope']) ? $validated['project_scope'] : $validated['remarks'],
                    'deal_amount' => $validated['deal_amount'],
                    'currency' => $lead->currency ?: 'USD',
                    'closed_date' => Carbon::today(),
                    'status' => 'won',
                    'notes' => $validated['remarks'],
                ]
            );

            $lead->update(['budget' => $validated['deal_amount']]);
        }

        return back()->with('success', 'Call log recorded and lead status updated successfully!');
    }
}
