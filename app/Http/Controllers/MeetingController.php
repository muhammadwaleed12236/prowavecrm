<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meeting;
use App\Models\Lead;
use Carbon\Carbon;

class MeetingController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Meeting::visibleTo($user)->with(['lead', 'user']);

        if ($request->filter === 'today') {
            $query->whereDate('scheduled_at', Carbon::today());
        } elseif ($request->filter === 'upcoming') {
            $query->where('scheduled_at', '>=', now())->where('status', 'scheduled');
        } elseif ($request->filter === 'completed') {
            $query->where('status', 'completed');
        }

        $meetings = $query->orderBy('scheduled_at', 'desc')->paginate(15)->withQueryString();

        // Alert badge count: today's meetings
        $todayCount = Meeting::visibleTo($user)
            ->whereDate('scheduled_at', Carbon::today())
            ->where('status', 'scheduled')
            ->count();

        return view('meetings.index', compact('meetings', 'todayCount'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'title' => 'required|string|max:255',
            'scheduled_at' => 'required|date',
            'meeting_link' => 'nullable|string|max:255',
            'agenda' => 'nullable|string',
        ]);

        $lead = Lead::findOrFail($validated['lead_id']);

        if (!$user->isSuperAdmin() && $lead->assigned_to !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        Meeting::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'title' => $validated['title'],
            'scheduled_at' => Carbon::parse($validated['scheduled_at']),
            'meeting_link' => $validated['meeting_link'],
            'status' => 'scheduled',
            'agenda' => $validated['agenda'],
        ]);

        $lead->update(['status' => 'meeting_scheduled']);

        return back()->with('success', 'Meeting scheduled successfully!');
    }

    public function updateStatus(Request $request, Meeting $meeting)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && $meeting->user_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'status' => 'required|in:scheduled,completed,cancelled,rescheduled,no_show',
            'outcome_notes' => 'nullable|string',
        ]);

        $meeting->update($validated);

        return back()->with('success', 'Meeting status updated successfully!');
    }
}
