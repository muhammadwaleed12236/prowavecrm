@extends('layouts.app')

@section('title', 'My Sales Dashboard')
@section('page_heading', 'Sales Rep Workspace')
@section('page_subheading', 'Your assigned leads, daily cold calling tasks & scheduled meetings')

@section('content')
<div class="space-y-6">

    <!-- Today's Scheduled Meetings Alert Banner (For this Agent) -->
    @if($todayMeetings->count() > 0)
        <div class="rounded-2xl p-5 bg-gradient-to-r from-amber-500/15 via-purple-500/10 to-indigo-500/10 border border-amber-500/30 backdrop-blur-xl">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <span class="flex h-3 w-3 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                    </span>
                    <h2 class="text-sm font-bold text-amber-300 uppercase tracking-wider">
                        Alert: You Have {{ $todayMeetings->count() }} Meeting(s) Scheduled Today!
                    </h2>
                </div>
                <a href="{{ route('meetings.index', ['filter' => 'today']) }}" class="text-xs font-semibold text-amber-400 hover:text-amber-300 underline">
                    My Meetings Schedule &rarr;
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($todayMeetings as $meet)
                    <div class="p-4 rounded-xl bg-slate-900/90 border border-amber-500/20 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-bold text-amber-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $meet->scheduled_at->format('h:i A') }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300">TODAY</span>
                            </div>
                            <div class="text-sm font-bold text-white">{{ $meet->lead->client_name }}</div>
                            <div class="text-xs text-slate-400">{{ $meet->title }}</div>
                            <div class="text-xs text-slate-400 mt-1">Phone: <span class="text-slate-200 font-mono">{{ $meet->lead->phone }}</span></div>
                        </div>
                        <div class="mt-3 pt-2 border-t border-slate-800 flex items-center justify-between">
                            @if($meet->meeting_link)
                                <a href="{{ $meet->meeting_link }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-400 hover:text-brand-300">
                                    <span>Join Link</span>
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            @else
                                <span class="text-xs text-slate-400">Direct Phone Call</span>
                            @endif
                            <a href="{{ route('leads.show', $meet->lead) }}" class="text-xs font-semibold text-slate-300 hover:text-white">
                                Lead Profile &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Sales Agent Performance Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Assigned Leads -->
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 relative">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">My Assigned Leads</div>
            <div class="text-3xl font-extrabold text-white">{{ $myLeadsCount }}</div>
            <div class="mt-2 text-xs text-brand-400">{{ $myNewLeads }} new uncalled leads</div>
        </div>

        <!-- Today Calls -->
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 relative">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Calls Logged Today</div>
            <div class="text-3xl font-extrabold text-cyan-400">{{ $myCallsToday }}</div>
            <div class="mt-2 text-xs text-slate-400">Keep up the momentum!</div>
        </div>

        <!-- Meetings Scheduled -->
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 relative">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Upcoming Meetings</div>
            <div class="text-3xl font-extrabold text-purple-400">{{ $myMeetingsScheduled }}</div>
            <div class="mt-2 text-xs text-purple-400">In your schedule</div>
        </div>

        <!-- My Deals Won -->
        <div class="p-5 rounded-2xl bg-gradient-to-br from-emerald-950/40 via-slate-900/90 to-slate-900/90 border border-emerald-500/30 relative">
            <div class="text-xs font-semibold text-emerald-400 uppercase tracking-wider mb-1">My Closed Revenue</div>
            <div class="text-3xl font-extrabold text-white">${{ number_format($myRevenueWon, 2) }}</div>
            <div class="mt-2 text-xs text-emerald-400 font-semibold">{{ $myDealsWon }} deals won</div>
        </div>
    </div>

    <!-- Active Calling Queue & Recent History -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Priority Calling Queue -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-400"></span>
                            Priority Calling Queue
                        </h2>
                        <p class="text-xs text-slate-400">Leads waiting for outreach, callback, or proposal</p>
                    </div>
                    <a href="{{ route('leads.index') }}" class="text-xs font-semibold text-brand-400 hover:text-brand-300">
                        View All ({{ $myLeadsCount }}) &rarr;
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($pendingLeads as $lead)
                        <div class="p-4 rounded-xl bg-slate-950/70 border border-slate-800 hover:border-brand-500/40 transition flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2.5 mb-1">
                                    <span class="text-sm font-bold text-white truncate">{{ $lead->client_name }}</span>
                                    @if($lead->company_name)
                                        <span class="text-xs text-slate-400 truncate">({{ $lead->company_name }})</span>
                                    @endif
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $lead->status_badge }}">
                                        {{ str_replace('_', ' ', $lead->status) }}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-400 flex items-center gap-3 sm:gap-4 flex-wrap pt-1">
                                    <a href="tel:{{ $lead->phone }}" class="flex items-center gap-1 font-mono font-bold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-lg border border-emerald-500/20 hover:bg-emerald-500/20">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        {{ $lead->phone }}
                                    </a>
                                    <span>{{ $lead->service_needed ?: 'General Inquiry' }}</span>
                                    @if($lead->budget)
                                        <span class="text-emerald-400 font-bold font-mono">${{ number_format($lead->budget, 0) }}</span>
                                    @endif
                                </div>
                            </div>

                            <a href="{{ route('leads.show', $lead) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-500 text-white shadow-md shadow-brand-600/20 transition whitespace-nowrap">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span>Log Call & Status</span>
                            </a>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 text-xs">No pending leads in queue. All caught up!</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- My Recent Call Logs -->
        <div class="space-y-6">
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-white">My Recent Call Activity</h2>
                    <span class="text-xs text-slate-400">Today</span>
                </div>

                <div class="space-y-3">
                    @forelse($recentCallLogs as $log)
                        <div class="p-3.5 rounded-xl bg-slate-950/80 border border-slate-800">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-bold text-white">{{ $log->lead->client_name }}</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider bg-slate-800 text-slate-300">
                                    {{ str_replace('_', ' ', $log->call_status) }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 line-clamp-2 italic mb-2">"{{ $log->remarks }}"</p>
                            <div class="text-[10px] text-slate-400">{{ $log->created_at->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 text-xs">No calls logged yet today.</div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
