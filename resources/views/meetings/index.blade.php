@extends('layouts.app')

@section('title', 'Meetings & Alerts')
@section('page_heading', 'Meetings & Next Steps')
@section('page_subheading', 'Upcoming client discovery calls, demos and reminders')

@section('content')
<div class="space-y-6">

    <!-- Filter Tabs & Stats -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/80 border border-slate-800/80 rounded-2xl p-4">
        <div class="flex items-center gap-2">
            <a href="{{ route('meetings.index') }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition {{ !request('filter') ? 'bg-brand-600 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                All Meetings ({{ $meetings->total() }})
            </a>
            <a href="{{ route('meetings.index', ['filter' => 'today']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ request('filter') === 'today' ? 'bg-amber-500 text-slate-950' : 'text-amber-400 hover:bg-amber-500/10' }}">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                Today's Schedule ({{ $todayCount }})
            </a>
            <a href="{{ route('meetings.index', ['filter' => 'upcoming']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition {{ request('filter') === 'upcoming' ? 'bg-brand-600 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                Upcoming Future
            </a>
            <a href="{{ route('meetings.index', ['filter' => 'completed']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition {{ request('filter') === 'completed' ? 'bg-brand-600 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                Completed
            </a>
        </div>
    </div>

    <!-- Meetings Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($meetings as $meeting)
            <div class="p-5 rounded-2xl bg-slate-900/80 border {{ $meeting->scheduled_at->isToday() ? 'border-amber-500/40 shadow-lg shadow-amber-500/5' : 'border-slate-800/80' }} flex flex-col justify-between transition hover:border-slate-700">
                <div>
                    <!-- Top Badge & Status -->
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider
                            @if($meeting->status === 'scheduled') bg-purple-500/20 text-purple-300 border border-purple-500/30
                            @elseif($meeting->status === 'completed') bg-emerald-500/20 text-emerald-300 border border-emerald-500/30
                            @else bg-slate-800 text-slate-400
                            @endif">
                            {{ $meeting->status }}
                        </span>

                        @if($meeting->scheduled_at->isToday())
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 animate-pulse">
                                TODAY ALERT
                            </span>
                        @endif
                    </div>

                    <h3 class="text-base font-bold text-white mb-1">{{ $meeting->title }}</h3>

                    <div class="space-y-1.5 text-xs text-slate-300 mb-4">
                        <div class="flex items-center gap-2">
                            <span class="text-slate-400">Prospect:</span>
                            <a href="{{ route('leads.show', $meeting->lead) }}" class="font-bold text-white hover:text-brand-400 underline">
                                {{ $meeting->lead->client_name }}
                            </a>
                        </div>
                        <div class="flex items-center gap-2 font-mono text-indigo-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ $meeting->scheduled_at->format('M d, Y @ h:i A') }}</span>
                        </div>
                        @if(auth()->user()->isSuperAdmin())
                            <div class="flex items-center gap-2">
                                <span class="text-slate-400">Assigned Rep:</span>
                                <span class="text-slate-200 font-semibold">{{ $meeting->user->name }}</span>
                            </div>
                        @endif
                        @if($meeting->agenda)
                            <p class="text-xs text-slate-400 italic pt-1 line-clamp-2">"{{ $meeting->agenda }}"</p>
                        @endif
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="pt-3 border-t border-slate-800/80 space-y-2.5">
                    @if($meeting->meeting_link)
                        <a href="{{ $meeting->meeting_link }}" target="_blank" class="w-full py-2 px-3 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-500 text-white flex items-center justify-center gap-2 transition">
                            <span>Open Meeting Link</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    @endif

                    <div class="flex items-center justify-between text-xs">
                        <a href="{{ route('leads.show', $meeting->lead) }}" class="text-slate-400 hover:text-white">
                            Lead Profile &rarr;
                        </a>

                        <form action="{{ route('meetings.update-status', $meeting) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <select name="status" onchange="this.form.submit()" class="px-2 py-1 rounded bg-slate-950 border border-slate-800 text-[11px] text-slate-300 focus:outline-none">
                                <option value="scheduled" {{ $meeting->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="completed" {{ $meeting->status === 'completed' ? 'selected' : '' }}>Mark Completed</option>
                                <option value="cancelled" {{ $meeting->status === 'cancelled' ? 'selected' : '' }}>Cancel</option>
                                <option value="rescheduled" {{ $meeting->status === 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 py-16 text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <div class="text-base font-semibold text-slate-300">No scheduled meetings found in this view</div>
                <p class="text-xs text-slate-500 mt-1">Book discovery meetings while updating cold call logs on any lead profile.</p>
            </div>
        @endforelse
    </div>

    @if($meetings->hasPages())
        <div class="p-4 bg-slate-900/60 rounded-xl border border-slate-800">
            {{ $meetings->links() }}
        </div>
    @endif

</div>
@endsection
