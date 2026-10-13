@extends('layouts.app')

@section('title', 'Daily Sales Performance Reports')
@section('page_heading', 'Daily Activity Reports')
@section('page_subheading', 'Automated daily breakdown of calls made, conversions & team performance')

@section('content')
<div class="space-y-6">

    <!-- Date Filter & Quick Actions -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form action="{{ route('reports.index') }}" method="GET" class="flex items-center gap-3 flex-wrap">
            <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Report Date:</label>
            <input type="date" name="date" value="{{ $date }}"
                   class="px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:outline-none focus:border-brand-500 font-mono">

            @if(auth()->user()->isSuperAdmin() && $agents->count() > 0)
                <select name="agent_id" class="px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:outline-none focus:border-brand-500">
                    <option value="">All Sales Reps</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                    @endforeach
                </select>
            @endif

            <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-500 text-white transition">
                Load Date &rarr;
            </button>
        </form>

        @if(auth()->user()->isSuperAdmin())
            <form action="{{ route('reports.generate') }}" method="POST">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition">
                    <svg class="w-4 h-4 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Re-calculate Daily Metrics Now</span>
                </button>
            </form>
        @endif
    </div>

    <!-- Daily Metrics Ribbon -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Calls Made</div>
            <div class="text-3xl font-black text-white">{{ $summary['total_calls'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">On {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</div>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800">
            <div class="text-[11px] font-semibold text-indigo-400 uppercase tracking-wider mb-1">Interested Leads</div>
            <div class="text-3xl font-black text-indigo-300">{{ $summary['interested'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">High conversion potential</div>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800">
            <div class="text-[11px] font-semibold text-amber-400 uppercase tracking-wider mb-1">Scheduled Callbacks</div>
            <div class="text-3xl font-black text-amber-300">{{ $summary['callbacks'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Follow-ups set</div>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800">
            <div class="text-[11px] font-semibold text-purple-400 uppercase tracking-wider mb-1">Meetings Booked</div>
            <div class="text-3xl font-black text-purple-300">{{ $summary['meetings'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Discovery calls & demos</div>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900/80 border border-emerald-500/30 bg-emerald-950/20">
            <div class="text-[11px] font-semibold text-emerald-400 uppercase tracking-wider mb-1">Deals Won Today</div>
            <div class="text-3xl font-black text-emerald-400">{{ $summary['deals_won'] }}</div>
            <div class="text-[11px] text-emerald-400/80 mt-1">Signed contracts</div>
        </div>
    </div>

    <!-- Daily Performance Breakdown Table -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-5 border-b border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-white">Representative Daily Scorecard</h3>
                <p class="text-xs text-slate-400">Automated performance summary for {{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}</p>
            </div>
            <span class="px-3 py-1 rounded-lg text-xs font-semibold bg-brand-500/10 text-brand-400 border border-brand-500/20">
                Auto-Compiled System Report
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-[11px] uppercase tracking-wider text-slate-400 bg-slate-950/40 border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-6 font-semibold">Sales Representative</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Total Cold Calls</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Interested Leads</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Callbacks</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Meetings Booked</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Deals Won</th>
                        <th class="py-3.5 px-6 font-semibold text-right">System Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($reports as $rep)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-4 px-6">
                                <div class="font-bold text-white">{{ $rep->user->name }}</div>
                                <div class="text-xs text-slate-400">{{ $rep->user->email }}</div>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="px-3 py-1 rounded-lg text-xs font-bold bg-slate-800 text-white">
                                    {{ $rep->total_calls }} calls
                                </span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-500/10 text-indigo-300">
                                    {{ $rep->interested_count }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-500/10 text-amber-300">
                                    {{ $rep->callbacks_count }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-purple-500/10 text-purple-300">
                                    {{ $rep->meetings_booked }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-500/20 text-emerald-300">
                                    {{ $rep->deals_won }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right text-xs text-slate-400">
                                <span class="text-emerald-400 font-semibold">✓ Verified</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                No activity recorded for this date.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detailed Individual Calls Audit Log for That Date -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-5 border-b border-slate-800">
            <h3 class="text-base font-bold text-white">Individual Call Logs Audit</h3>
            <p class="text-xs text-slate-400">Chronological stream of calls made on {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</p>
        </div>

        <div class="p-5 space-y-3 max-h-[420px] overflow-y-auto">
            @forelse($detailedLogs as $log)
                <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-bold text-white">{{ $log->lead->client_name }}</span>
                            <span class="text-slate-400">({{ $log->lead->phone }})</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider 
                                @if($log->call_status === 'interested') bg-indigo-500/20 text-indigo-300
                                @elseif($log->call_status === 'meeting_scheduled') bg-purple-500/20 text-purple-300
                                @elseif($log->call_status === 'deal_won') bg-emerald-500/20 text-emerald-300
                                @else bg-slate-800 text-slate-300
                                @endif">
                                {{ str_replace('_', ' ', $log->call_status) }}
                            </span>
                        </div>
                        <p class="text-slate-300 italic">"{{ $log->remarks }}"</p>
                    </div>
                    <div class="text-right text-[11px] text-slate-400 md:min-w-[160px]">
                        <div>Rep: <strong class="text-slate-200">{{ $log->user->name }}</strong></div>
                        <div class="font-mono text-slate-400">{{ $log->created_at->format('h:i:s A') }}</div>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-slate-400 text-xs">No calls logged on this specific date.</div>
            @endforelse
        </div>
    </div>

</div>
@endsection
