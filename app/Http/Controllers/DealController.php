<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deal;
use App\Models\Lead;
use Carbon\Carbon;

class DealController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Deal::visibleTo($user)->with(['lead', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                  ->orWhere('project_title', 'like', "%{$search}%");
            });
        }

        $deals = $query->latest('closed_date')->paginate(12)->withQueryString();

        $totalWonAmount = Deal::visibleTo($user)->sum('deal_amount');
        $totalDealsCount = Deal::visibleTo($user)->count();
        $thisMonthWonAmount = Deal::visibleTo($user)
            ->whereMonth('closed_date', Carbon::now()->month)
            ->whereYear('closed_date', Carbon::now()->year)
            ->sum('deal_amount');

        return view('deals.index', compact('deals', 'totalWonAmount', 'totalDealsCount', 'thisMonthWonAmount'));
    }

    public function show(Deal $deal)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && $deal->user_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $deal->load(['lead', 'user']);

        return view('deals.show', compact('deal'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'project_title' => 'required|string|max:255',
            'deal_amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'closed_date' => 'required|date',
            'project_scope' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $lead = Lead::findOrFail($validated['lead_id']);

        if (!$user->isSuperAdmin() && $lead->assigned_to !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $deal = Deal::updateOrCreate(
            ['lead_id' => $lead->id],
            [
                'user_id' => $user->id,
                'client_name' => $lead->client_name . ($lead->company_name ? " ({$lead->company_name})" : ''),
                'project_title' => $validated['project_title'],
                'deal_amount' => $validated['deal_amount'],
                'currency' => $validated['currency'],
                'closed_date' => Carbon::parse($validated['closed_date']),
                'status' => 'won',
                'project_scope' => $validated['project_scope'],
                'notes' => $validated['notes'],
            ]
        );

        $lead->update([
            'status' => 'deal_won',
            'budget' => $validated['deal_amount'],
        ]);

        return redirect()->route('deals.index')->with('success', 'Deal won successfully recorded!');
    }
}
