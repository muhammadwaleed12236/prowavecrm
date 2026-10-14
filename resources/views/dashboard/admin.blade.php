@extends('layouts.app')

@section('title', 'Super Admin Overview')
@section('page_heading', 'Command Center')
@section('page_subheading', 'Real-time company-wide sales activity, meeting schedule & revenue won')

@section('content')
<div class="space-y-6">

    <!-- Today's Meetings Alert Banner (If Any) -->
    @if($todayMeetings->count() > 0)
        <div class="rounded-2xl p-5 bg-gradient-to-r from-amber-500/15 via-purple-500/10 to-indigo-500/10 border border-amber-500/30 backdrop-blur-xl">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <span class="flex h-3 w-3 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                    </span>
                    <h2 class="text-sm font-bold text-amber-300 uppercase tracking-wider">
                        Meetings Scheduled Today ({{ $todayMeetings->count() }})
                    </h2>
                </div>
                <a href="{{ route('meetings.index', ['filter' => 'today']) }}" class="text-xs font-semibold text-amber-400 hover:text-amber-300 underline">
                    View Calendar &rarr;
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($todayMeetings as $meet)
                    <div class="p-3.5 rounded-xl bg-slate-900/90 border border-amber-500/20 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-bold text-amber-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $meet->scheduled_at->format('h:i A') }}
                                </span>
                                <span class="text-[11px] text-slate-400">Rep: <strong class="text-slate-200">{{ $meet->user->name }}</strong></span>
                            </div>
                            <div class="text-sm font-bold text-white truncate">{{ $meet->lead->client_name }}</div>
                            <div class="text-xs text-slate-400 truncate">{{ $meet->title }}</div>
                        </div>
                        @if($meet->meeting_link)
                            <div class="mt-3 pt-2 border-t border-slate-800">
                                <a href="{{ $meet->meeting_link }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-400 hover:text-brand-300">
                                    <span>Join Meeting</span>
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Key Metrics Ribbon -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-2.5 sm:gap-4">
        <!-- Total Leads -->
        <div class="p-3.5 sm:p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 relative overflow-hidden group hover:border-slate-700 transition glass-card-hover">
            <div class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1 truncate">Total Leads Pool</div>
            <div class="text-xl sm:text-3xl font-extrabold text-white mobile-num">{{ $totalLeads }}</div>
            <div class="mt-1 sm:mt-2 text-[10px] sm:text-xs text-indigo-400 truncate">{{ $newLeadsCount }} uncontacted</div>
        </div>

        <!-- In Pipeline -->
        <div class="p-3.5 sm:p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 relative overflow-hidden group hover:border-slate-700 transition glass-card-hover">
            <div class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1 truncate">Active Pipeline</div>
            <div class="text-xl sm:text-3xl font-extrabold text-cyan-400 mobile-num">{{ $interestedCount }}</div>
            <div class="mt-1 sm:mt-2 text-[10px] sm:text-xs text-slate-400 truncate">Callbacks & Interest</div>
        </div>

        <!-- Meetings Scheduled -->
        <div class="p-3.5 sm:p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 relative overflow-hidden group hover:border-slate-700 transition glass-card-hover">
            <div class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1 truncate">Meetings Booked</div>
            <div class="text-xl sm:text-3xl font-extrabold text-purple-400 mobile-num">{{ $meetingsScheduledCount }}</div>
            <div class="mt-1 sm:mt-2 text-[10px] sm:text-xs text-purple-400 truncate">{{ $upcomingMeetings->count() }} upcoming</div>
        </div>

        <!-- Deals Won -->
        <div class="p-3.5 sm:p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 relative overflow-hidden group hover:border-slate-700 transition glass-card-hover">
            <div class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1 truncate">Deals Won</div>
            <div class="text-xl sm:text-3xl font-extrabold text-emerald-400 mobile-num">{{ $dealsWonCount }}</div>
            <div class="mt-1 sm:mt-2 text-[10px] sm:text-xs text-emerald-400 truncate">Closed contracts</div>
        </div>

        <!-- Total Revenue -->
        <div class="col-span-2 lg:col-span-1 p-3.5 sm:p-5 rounded-2xl bg-gradient-to-br from-emerald-950/40 via-slate-900/90 to-slate-900/90 border border-emerald-500/30 relative overflow-hidden glass-card-hover">
            <div class="text-[10px] sm:text-xs font-semibold text-emerald-400 uppercase tracking-wider mb-1 truncate">Total Revenue Won</div>
            <div class="text-xl sm:text-3xl font-extrabold text-white mobile-num font-mono">${{ number_format($totalRevenueWon, 0) }}</div>
            <div class="mt-1 sm:mt-2 text-[10px] sm:text-xs text-slate-400 truncate">ProWave Contracts</div>
        </div>
    </div>

    <!-- Main 2-Column Split: Team Leaderboard & Live Calling Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Sales Representatives Performance Leaderboard -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-white">Sales Team Performance Leaderboard</h2>
                        <p class="text-xs text-slate-400">Real-time breakdown of assigned leads, daily calls, and deals won</p>
                    </div>
                    <a href="{{ route('reports.index') }}" class="text-xs font-semibold text-brand-400 hover:text-brand-300">
                        Detailed Reports &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-[11px] uppercase tracking-wider text-slate-400 border-b border-slate-800">
                            <tr>
                                <th class="pb-3 font-semibold">Sales Representative</th>
                                <th class="pb-3 font-semibold text-center">Assigned Leads</th>
                                <th class="pb-3 font-semibold text-center">Calls Today</th>
                                <th class="pb-3 font-semibold text-center">Meetings Booked</th>
                                <th class="pb-3 font-semibold text-right">Won Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @forelse($agents as $agent)
                                <tr class="hover:bg-slate-800/30 transition">
                                    <td class="py-3.5 pr-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 rounded-lg bg-indigo-500/20 text-indigo-300 font-bold flex items-center justify-center text-xs">
                                                {{ strtoupper(substr($agent->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-semibold text-white">{{ $agent->name }}</div>
                                                <div class="text-[11px] text-slate-400">{{ $agent->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 text-center">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-800 text-slate-200">
                                            {{ $agent->leads_count }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 text-center">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                            {{ $agent->calls_today }} calls
                                        </span>
                                    </td>
                                    <td class="py-3.5 text-center">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-purple-500/10 text-purple-400 border border-purple-500/20">
                                            {{ $agent->meetings_today }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 text-right">
                                        <span class="font-bold text-emerald-400">
                                            ${{ number_format($agent->total_revenue ?? 0, 2) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-400">No sales agents registered yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Deals Won Section -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            Won Projects & Client Budgets
                        </h2>
                        <p class="text-xs text-slate-400">High-value closed contracts won by your sales team</p>
                    </div>
                    <a href="{{ route('deals.index') }}" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300">
                        All Won Deals &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($recentDeals as $deal)
                        <div class="p-4 rounded-xl bg-slate-950/60 border border-emerald-500/20 hover:border-emerald-500/40 transition flex flex-col justify-between">
                            <div>
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <span class="text-sm font-bold text-white">{{ $deal->client_name }}</span>
                                    <span class="px-2 py-0.5 rounded-md text-xs font-extrabold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                        ${{ number_format($deal->deal_amount, 2) }}
                                    </span>
                                </div>
                                <div class="text-xs font-semibold text-brand-300 mb-1">{{ $deal->project_title }}</div>
                                <p class="text-xs text-slate-400 line-clamp-2">{{ $deal->project_scope ?: 'No scope details added' }}</p>
                            </div>
                            <div class="mt-3 pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                                <span>Closed by: <strong class="text-slate-300">{{ $deal->user->name }}</strong></span>
                                <span>{{ $deal->closed_date->format('M d, Y') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 py-8 text-center text-slate-400">No deals closed yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Live Calling Feed & Activity Stream -->
        <div class="space-y-6">
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-pulse"></span>
                        Live Calling Stream
                    </h2>
                    <span class="text-[11px] text-slate-400">All Agents</span>
                </div>

                <div class="space-y-3.5 max-h-[580px] overflow-y-auto pr-1">
                    @forelse($recentCallLogs as $log)
                        <div class="p-3.5 rounded-xl bg-slate-950/80 border border-slate-800/80 hover:border-slate-700 transition">
                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                <span class="text-xs font-bold text-white truncate">{{ $log->lead->client_name }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider 
                                    @if($log->call_status === 'interested') bg-indigo-500/20 text-indigo-300 border border-indigo-500/30
                                    @elseif($log->call_status === 'meeting_scheduled') bg-purple-500/20 text-purple-300 border border-purple-500/30
                                    @elseif($log->call_status === 'deal_won') bg-emerald-500/20 text-emerald-300 border border-emerald-500/30
                                    @elseif($log->call_status === 'callback') bg-amber-500/20 text-amber-300 border border-amber-500/30
                                    @else bg-slate-800 text-slate-300
                                    @endif">
                                    {{ str_replace('_', ' ', $log->call_status) }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-300 line-clamp-2 mb-2 italic">"{{ $log->remarks }}"</p>
                            <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1.5 border-t border-slate-800/60">
                                <span>Agent: <strong class="text-slate-200">{{ $log->user->name }}</strong></span>
                                <span>{{ $log->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 text-xs">No calls logged today yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
