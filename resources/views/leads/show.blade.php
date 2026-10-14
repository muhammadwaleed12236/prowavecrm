@extends('layouts.app')

@section('title', 'Lead: ' . $lead->client_name)
@section('page_heading', $lead->client_name)
@section('page_subheading', 'Cold Calling Workspace, Status Updates & Meeting Scheduler')

@section('content')
<div class="space-y-6" x-data="{ callStatus: 'interested', activeTab: 'calls' }">

    <!-- Top Prospect Header Card -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 sm:p-6 shadow-xl relative overflow-hidden glass-card">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 sm:gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                    <h2 class="text-lg sm:text-2xl font-extrabold text-white leading-tight">{{ $lead->client_name }}</h2>
                    @if($lead->lead_type === 'outsource')
                        <span class="text-[10px] sm:text-xs font-bold px-2 sm:px-3 py-0.5 sm:py-1 rounded-lg bg-purple-500/20 text-purple-300 border border-purple-500/30 uppercase tracking-wider">
                            Outsource Lead
                        </span>
                    @else
                        <span class="text-[10px] sm:text-xs font-bold px-2 sm:px-3 py-0.5 sm:py-1 rounded-lg bg-brand-500/20 text-brand-300 border border-brand-500/30 uppercase tracking-wider">
                            Own Lead
                        </span>
                    @endif
                    <span class="px-2 sm:px-3 py-0.5 sm:py-1 rounded-lg text-[10px] sm:text-xs font-bold uppercase tracking-wider {{ $lead->status_badge }}">
                        {{ str_replace('_', ' ', $lead->status) }}
                    </span>
                </div>

                <div class="flex items-center gap-4 sm:gap-6 text-[11px] sm:text-xs text-slate-300 flex-wrap">
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <span class="text-slate-400">Phone:</span>
                        <a href="tel:{{ $lead->phone }}" class="font-mono font-bold text-white bg-slate-950 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-lg border border-slate-800 hover:border-brand-500 transition flex items-center gap-1.5">
                            <svg class="w-3 sm:w-3.5 h-3 sm:h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ $lead->phone }}
                        </a>
                    </div>

                    @if($lead->email)
                        <div class="flex items-center gap-1.5">
                            <span class="text-slate-400">Email:</span>
                            <a href="mailto:{{ $lead->email }}" class="text-brand-400 hover:underline">{{ $lead->email }}</a>
                        </div>
                    @endif

                    @if($lead->notes)
                        <div class="flex items-center gap-1.5">
                            <span class="text-slate-400">Remarks:</span>
                            <span class="text-slate-300 italic">{{ Str::limit($lead->notes, 60) }}</span>
                        </div>
                    @endif
                </div>

                <!-- 📱 Mobile Prominent Direct Dial Banner -->
                <div class="md:hidden pt-1.5">
                    <a href="tel:{{ $lead->phone }}" 
                       class="flex items-center justify-center gap-2 w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/30 transition">
                        <svg class="w-4 h-4 text-white animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>Call Client Now: {{ $lead->phone }}</span>
                    </a>
                </div>
            </div>

            <!-- Lead Assignment & Edit Actions -->
            <div class="flex items-center gap-3">
                @if(auth()->user()->isSuperAdmin())
                    <form action="{{ route('leads.assign', $lead) }}" method="POST" class="flex items-center gap-2">
                        @csrf
                        <div class="text-xs text-slate-400">Assigned To:</div>
                        <select name="assigned_to" onchange="this.form.submit()"
                                class="px-3 py-2 rounded-xl text-xs bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-brand-500">
                            <option value="">Unassigned</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ $lead->assigned_to == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @else
                    <div class="text-xs text-slate-400">
                        Assigned To: <strong class="text-white">{{ $lead->assignedAgent ? $lead->assignedAgent->name : 'You' }}</strong>
                    </div>
                @endif

                <a href="{{ route('leads.edit', $lead) }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 transition">
                    Edit Details
                </a>
            </div>
        </div>
    </div>

    <!-- 2 Column Layout: Interactive Cold Call Logger Form & Activity Timeline -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left: Cold Calling Log & Action Station (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-slate-900/90 border border-brand-500/30 rounded-2xl p-6 shadow-xl relative overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <span class="flex h-2.5 w-2.5 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-brand-500"></span>
                        </span>
                        Cold Call & Status Updater
                    </h3>
                    <span class="text-[11px] text-slate-400">One-click Sync</span>
                </div>

                <form action="{{ route('call-logs.store', $lead) }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Call Outcome / Status Select -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Call Status / Outcome</label>
                        <select name="call_status" x-model="callStatus" required
                                class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500 font-medium">
                            <option value="answered">Answered / General Conversation</option>
                            <option value="interested">Interested - Wants More Info</option>
                            <option value="callback">Callback Requested</option>
                            <option value="meeting_scheduled">Meeting Scheduled / Demo Booked</option>
                            <option value="deal_won">Deal Won / Project Closed!</option>
                            <option value="no_answer">No Answer / Rang Out</option>
                            <option value="busy">Busy / Voicemail</option>
                            <option value="not_interested">Not Interested</option>
                            <option value="wrong_number">Wrong Number / Invalid</option>
                        </select>
                    </div>

                    <!-- Conditional Section 1: Callback Requested -->
                    <div x-show="callStatus === 'callback'" x-cloak class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 space-y-3">
                        <div class="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Callback Date & Time
                        </div>
                        <input type="datetime-local" name="callback_at"
                               class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-white text-xs focus:outline-none focus:border-amber-500">
                    </div>

                    <!-- Conditional Section 2: Meeting Scheduled Setup -->
                    <div x-show="callStatus === 'meeting_scheduled'" x-cloak class="p-4 rounded-xl bg-purple-500/10 border border-purple-500/30 space-y-3">
                        <div class="text-xs font-bold text-purple-300 uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Next Meeting Alert Setup
                        </div>

                        <div>
                            <label class="block text-[11px] text-slate-300 mb-1">Meeting Title</label>
                            <input type="text" name="meeting_title" placeholder="e.g. Discovery & Demo Call with {{ $lead->client_name }}"
                                   value="Discovery & Demo Call - {{ $lead->client_name }}"
                                   class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-white text-xs focus:outline-none focus:border-purple-500">
                        </div>

                        <div>
                            <label class="block text-[11px] text-slate-300 mb-1">Scheduled Date & Time *</label>
                            <input type="datetime-local" name="meeting_time"
                                   class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-white text-xs focus:outline-none focus:border-purple-500">
                        </div>

                        <div>
                            <label class="block text-[11px] text-slate-300 mb-1">Meeting Link (Google Meet / Zoom)</label>
                            <input type="text" name="meeting_link" placeholder="https://meet.google.com/xxx-xxxx-xxx"
                                   class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-white text-xs focus:outline-none focus:border-purple-500">
                        </div>

                        <div>
                            <label class="block text-[11px] text-slate-300 mb-1">Meeting Agenda</label>
                            <textarea name="meeting_agenda" rows="2" placeholder="Brief scope to discuss during meeting..."
                                      class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-white text-xs focus:outline-none focus:border-purple-500"></textarea>
                        </div>
                    </div>

                    <!-- Conditional Section 3: Deal Won & Budget Showcase -->
                    <div x-show="callStatus === 'deal_won'" x-cloak class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 space-y-3">
                        <div class="text-xs font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Deal Won & Project Contract Details
                        </div>

                        <div>
                            <label class="block text-[11px] text-slate-300 mb-1">Final Agreed Budget ($ USD) *</label>
                            <input type="number" step="0.01" name="deal_amount" value="{{ $lead->budget }}" placeholder="e.g. 5000.00"
                                   class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-white font-mono font-bold text-xs focus:outline-none focus:border-emerald-500">
                        </div>

                        <div>
                            <label class="block text-[11px] text-slate-300 mb-1">Project Title</label>
                            <input type="text" name="project_title" value="{{ $lead->service_needed ?: 'Custom Software Development' }}"
                                   class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-white text-xs focus:outline-none focus:border-emerald-500">
                        </div>

                        <div>
                            <label class="block text-[11px] text-slate-300 mb-1">Project Scope & Deliverables</label>
                            <textarea name="project_scope" rows="2" placeholder="Describe scope, milestones, deliverables for Super Admin..."
                                      class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-white text-xs focus:outline-none focus:border-emerald-500"></textarea>
                        </div>
                    </div>

                    <!-- Remarks / Call Notes -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Call Remarks & Summary *</label>
                        <textarea name="remarks" rows="3" required placeholder="What happened on the call? Client objections, answers, next step details..."
                                  class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:outline-none focus:border-brand-500"></textarea>
                    </div>

                    <!-- Call Duration -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] text-slate-400 mb-1">Call Duration (Seconds)</label>
                            <input type="number" name="call_duration_seconds" value="120"
                                   class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-white text-xs">
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3 px-4 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-500 text-white shadow-lg shadow-brand-500/25 transition">
                        Save Call Log & Sync Status &rarr;
                    </button>
                </form>
            </div>

            <!-- Lead Scope Quick Card -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 space-y-3">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Prospect Background</h4>
                <div class="text-xs text-slate-300 space-y-2">
                    <div>
                        <span class="text-slate-400">Service Required:</span>
                        <span class="font-semibold text-white ml-1">{{ $lead->service_needed ?: 'Not Specified' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400">Estimated Budget:</span>
                        <span class="font-bold text-emerald-400 ml-1">
                            {{ $lead->budget ? '$' . number_format($lead->budget, 2) : 'Pending' }}
                        </span>
                    </div>
                    @if($lead->notes)
                        <div class="pt-2 border-t border-slate-800">
                            <span class="text-slate-400 block mb-1">Original Lead Notes:</span>
                            <p class="text-slate-300 bg-slate-950 p-2.5 rounded-lg border border-slate-800">{{ $lead->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Interactive Activity Timeline, Scheduled Meetings & Deal (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                <!-- Navigation Tabs (Mobile Horizontal Swipeable) -->
                <div class="flex items-center border-b border-slate-800 bg-slate-950/40 px-4 sm:px-6 pt-3 gap-4 sm:gap-6 overflow-x-auto whitespace-nowrap">
                    <button @click="activeTab = 'calls'" 
                            :class="activeTab === 'calls' ? 'text-brand-400 border-b-2 border-brand-500' : 'text-slate-400 hover:text-white'"
                            class="pb-3 text-xs font-bold uppercase tracking-wider transition whitespace-nowrap">
                        Call Logs History ({{ $lead->callLogs->count() }})
                    </button>
                    <button @click="activeTab = 'meetings'" 
                            :class="activeTab === 'meetings' ? 'text-brand-400 border-b-2 border-brand-500' : 'text-slate-400 hover:text-white'"
                            class="pb-3 text-xs font-bold uppercase tracking-wider transition whitespace-nowrap">
                        Meetings ({{ $lead->meetings->count() }})
                    </button>
                    @if($lead->deal)
                        <button @click="activeTab = 'deal'" 
                                :class="activeTab === 'deal' ? 'text-emerald-400 border-b-2 border-emerald-500' : 'text-slate-400 hover:text-white'"
                                class="pb-3 text-xs font-bold uppercase tracking-wider transition flex items-center gap-1.5 whitespace-nowrap">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            Won Project Contract
                        </button>
                    @endif
                </div>

                <!-- Tab 1: Call Logs Timeline -->
                <div x-show="activeTab === 'calls'" class="p-4 sm:p-6 space-y-4">
                    @forelse($lead->callLogs as $log)
                        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 relative">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider 
                                        @if($log->call_status === 'interested') bg-indigo-500/20 text-indigo-300 border border-indigo-500/30
                                        @elseif($log->call_status === 'meeting_scheduled') bg-purple-500/20 text-purple-300 border border-purple-500/30
                                        @elseif($log->call_status === 'deal_won') bg-emerald-500/20 text-emerald-300 border border-emerald-500/30
                                        @elseif($log->call_status === 'callback') bg-amber-500/20 text-amber-300 border border-amber-500/30
                                        @else bg-slate-800 text-slate-300
                                        @endif">
                                        {{ str_replace('_', ' ', $log->call_status) }}
                                    </span>
                                    <span class="text-xs text-slate-400">by <strong class="text-slate-200">{{ $log->user->name }}</strong></span>
                                </div>
                                <span class="text-[11px] text-slate-400">{{ $log->created_at->format('M d, Y - h:i A') }}</span>
                            </div>

                            <p class="text-xs text-slate-200 leading-relaxed">{{ $log->remarks }}</p>

                            @if($log->callback_at)
                                <div class="mt-2.5 pt-2 border-t border-slate-800/80 text-[11px] text-amber-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Scheduled Callback: <strong>{{ $log->callback_at->format('M d, Y h:i A') }}</strong>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="py-12 text-center text-slate-400 text-xs">
                            No calls logged yet. Use the form on the left to record the first cold call!
                        </div>
                    @endforelse
                </div>

                <!-- Tab 2: Meetings List -->
                <div x-show="activeTab === 'meetings'" x-cloak class="p-6 space-y-4">
                    @forelse($lead->meetings as $meeting)
                        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 flex flex-col justify-between gap-3">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-bold text-white">{{ $meeting->title }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                        @if($meeting->status === 'scheduled') bg-purple-500/20 text-purple-300 border border-purple-500/30
                                        @elseif($meeting->status === 'completed') bg-emerald-500/20 text-emerald-300
                                        @else bg-slate-800 text-slate-400
                                        @endif">
                                        {{ $meeting->status }}
                                    </span>
                                </div>
                                <div class="text-xs font-mono text-purple-400 flex items-center gap-1.5 mb-2">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $meeting->scheduled_at->format('l, F d, Y @ h:i A') }}
                                </div>
                                @if($meeting->agenda)
                                    <p class="text-xs text-slate-400 italic">"{{ $meeting->agenda }}"</p>
                                @endif
                            </div>

                            <div class="pt-3 border-t border-slate-800 flex items-center justify-between">
                                @if($meeting->meeting_link)
                                    <a href="{{ $meeting->meeting_link }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-400 hover:underline">
                                        <span>Launch Meeting Link</span>
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">Phone Meeting</span>
                                @endif

                                <!-- Status Updater -->
                                <form action="{{ route('meetings.update-status', $meeting) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" class="px-2 py-1 rounded bg-slate-900 border border-slate-700 text-[11px] text-slate-300">
                                        <option value="scheduled" {{ $meeting->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                        <option value="completed" {{ $meeting->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ $meeting->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        <option value="rescheduled" {{ $meeting->status === 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                                    </select>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-slate-400 text-xs">No meetings scheduled for this prospect yet.</div>
                    @endforelse
                </div>

                <!-- Tab 3: Won Deal Details -->
                @if($lead->deal)
                    <div x-show="activeTab === 'deal'" x-cloak class="p-6 space-y-4">
                        <div class="p-5 rounded-xl bg-gradient-to-br from-emerald-950/40 to-slate-950 border border-emerald-500/30">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <div class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Won Project Contract</div>
                                    <h4 class="text-lg font-bold text-white">{{ $lead->deal->project_title }}</h4>
                                </div>
                                <div class="text-2xl font-extrabold text-emerald-400 font-mono">
                                    ${{ number_format($lead->deal->deal_amount, 2) }}
                                </div>
                            </div>
                            <div class="text-xs text-slate-300 space-y-2 mb-4">
                                <div>
                                    <span class="text-slate-400">Closed Date:</span>
                                    <span class="text-white font-medium ml-1">{{ $lead->deal->closed_date->format('F d, Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400">Closing Sales Rep:</span>
                                    <span class="text-white font-semibold ml-1">{{ $lead->deal->user->name }}</span>
                                </div>
                                @if($lead->deal->project_scope)
                                    <div class="pt-2 border-t border-slate-800">
                                        <span class="text-slate-400 block mb-1">Scope & Deliverables:</span>
                                        <p class="text-slate-200 bg-slate-950/80 p-3 rounded-lg border border-slate-800">{{ $lead->deal->project_scope }}</p>
                                    </div>
                                @endif
                            </div>
                            <a href="{{ route('deals.show', $lead->deal) }}" class="inline-block text-xs font-bold text-emerald-400 hover:underline">
                                View Full Super Admin Contract Record &rarr;
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>

</div>
@endsection
