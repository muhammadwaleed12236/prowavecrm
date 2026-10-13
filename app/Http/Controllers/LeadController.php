<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\User;
use App\Models\CallLog;
use App\Models\Meeting;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Lead::visibleTo($user)->with(['assignedAgent', 'callLogs' => function($q) {
            $q->latest()->take(1);
        }]);

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        // Only super admin can filter by assigned agent
        if ($user->isSuperAdmin() && $request->filled('agent_id')) {
            if ($request->agent_id === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->agent_id);
            }
        }

        $leads = $query->latest('updated_at')->paginate(15)->withQueryString();
        $agents = $user->isSuperAdmin() ? User::where('role', 'sales_agent')->get() : collect();

        return view('leads.index', compact('leads', 'agents'));
    }

    public function create()
    {
        $user = auth()->user();
        $agents = $user->isSuperAdmin() ? User::where('role', 'sales_agent')->where('status', 'active')->get() : collect();

        return view('leads.create', compact('agents'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:50',
            'country' => 'nullable|string|max:100',
            'service_needed' => 'nullable|string|max:255',
            'source' => 'required|string|max:100',
            'budget' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $validated['created_by'] = $user->id;

        // If sales agent creates lead, it assigns to them automatically
        if (!$user->isSuperAdmin()) {
            $validated['assigned_to'] = $user->id;
        }

        $validated['status'] = 'new';

        $lead = Lead::create($validated);

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Lead created successfully!');
    }

    public function show(Lead $lead)
    {
        $user = auth()->user();

        // Enforce data privacy: Agents can only view their own assigned leads
        if (!$user->isSuperAdmin() && $lead->assigned_to !== $user->id) {
            abort(403, 'Unauthorized. You can only view leads assigned to you.');
        }

        $lead->load(['assignedAgent', 'callLogs.user', 'meetings.user', 'deal.user']);
        $agents = $user->isSuperAdmin() ? User::where('role', 'sales_agent')->where('status', 'active')->get() : collect();

        return view('leads.show', compact('lead', 'agents'));
    }

    public function edit(Lead $lead)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && $lead->assigned_to !== $user->id) {
            abort(403, 'Unauthorized. You can only edit leads assigned to you.');
        }

        $agents = $user->isSuperAdmin() ? User::where('role', 'sales_agent')->where('status', 'active')->get() : collect();

        return view('leads.edit', compact('lead', 'agents'));
    }

    public function update(Request $request, Lead $lead)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && $lead->assigned_to !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:50',
            'country' => 'nullable|string|max:100',
            'service_needed' => 'nullable|string|max:255',
            'source' => 'required|string|max:100',
            'status' => 'required|in:new,contacted,interested,callback,meeting_scheduled,proposal_sent,deal_won,lost,not_interested',
            'budget' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        // If not super admin, protect assigned_to from being changed
        if (!$user->isSuperAdmin()) {
            unset($validated['assigned_to']);
        }

        $lead->update($validated);

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Lead updated successfully!');
    }

    public function assign(Request $request, Lead $lead)
    {
        // Only Super Admin can assign/re-assign
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $lead->update([
            'assigned_to' => $request->assigned_to,
        ]);

        $agentName = $lead->assignedAgent ? $lead->assignedAgent->name : 'Unassigned';

        return back()->with('success', "Lead assigned to {$agentName} successfully!");
    }

    public function destroy(Lead $lead)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only Super Admin can delete leads.');
        }

        $lead->delete();

        return redirect()->route('leads.index')->with('success', 'Lead deleted successfully.');
    }
}
