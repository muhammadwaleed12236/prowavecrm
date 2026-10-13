@extends('layouts.app')

@section('title', 'Leads Management')
@section('page_heading', auth()->user()->isSuperAdmin() ? 'All Leads Pool' : 'My Assigned Leads')
@section('page_subheading', 'Manage prospects, cold calling pipelines and assignment')

@section('content')
<div class="space-y-6">

    <!-- Top Quick Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/80 border border-slate-800/80 rounded-2xl p-4">
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <span>Showing <strong class="text-white">{{ $leads->total() }}</strong> prospects</span>
            <span>•</span>
            <span class="text-emerald-400">Excel / CSV Bulk Import Ready</span>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('leads.import.show') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 border border-emerald-500/30 transition shadow-sm">
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span>Import from Excel / CSV</span>
            </a>
            <a href="{{ route('leads.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-500 text-white shadow-lg shadow-brand-600/20 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Single Lead</span>
            </a>
        </div>
    </div>

    <!-- Filters & Action Header -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5">
        <form action="{{ route('leads.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search -->
            <div class="lg:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search client name, company, phone, email..."
                       class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:outline-none focus:border-brand-500">
            </div>

            <!-- Status Filter -->
            <div>
                <select name="status" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:outline-none focus:border-brand-500">
                    <option value="">All Statuses</option>
                    <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>New</option>
                    <option value="contacted" {{ request('status') === 'contacted' ? 'selected' : '' }}>Contacted</option>
                    <option value="interested" {{ request('status') === 'interested' ? 'selected' : '' }}>Interested</option>
                    <option value="callback" {{ request('status') === 'callback' ? 'selected' : '' }}>Callback</option>
                    <option value="meeting_scheduled" {{ request('status') === 'meeting_scheduled' ? 'selected' : '' }}>Meeting Scheduled</option>
                    <option value="deal_won" {{ request('status') === 'deal_won' ? 'selected' : '' }}>Deal Won</option>
                    <option value="not_interested" {{ request('status') === 'not_interested' ? 'selected' : '' }}>Not Interested</option>
                </select>
            </div>

            <!-- Agent Filter (Super Admin only) -->
            @if(auth()->user()->isSuperAdmin())
                <div>
                    <select name="agent_id" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:outline-none focus:border-brand-500">
                        <option value="">All Sales Reps</option>
                        <option value="unassigned" {{ request('agent_id') === 'unassigned' ? 'selected' : '' }}>[Unassigned Leads]</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-500 text-white transition">
                    Filter Leads
                </button>
                @if(request()->anyFilled(['search', 'status', 'agent_id', 'source']))
                    <a href="{{ route('leads.index') }}" class="py-2.5 px-3 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Leads List (Desktop Table & Mobile Cards) -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl overflow-hidden shadow-xl">
        
        <!-- 📱 MOBILE CARDS VIEW (Visible only on screens < 768px) -->
        <div class="block md:hidden p-3.5 space-y-3">
            @forelse($leads as $lead)
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800/90 shadow-md space-y-3">
                    <!-- Card Header -->
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <a href="{{ route('leads.show', $lead) }}" class="text-base font-extrabold text-white hover:text-brand-400 block truncate">
                                {{ $lead->client_name }}
                            </a>
                            @if($lead->company_name)
                                <div class="text-xs text-slate-400 truncate">{{ $lead->company_name }}</div>
                            @endif
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider whitespace-nowrap {{ $lead->status_badge }}">
                            {{ str_replace('_', ' ', $lead->status) }}
                        </span>
                    </div>

                    <!-- Direct Tap-to-Dial Phone Button -->
                    <a href="tel:{{ $lead->phone }}" 
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 transition font-mono text-xs font-bold">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>{{ $lead->phone }}</span>
                        </span>
                        <span class="text-[10px] uppercase tracking-wider font-sans bg-emerald-500/20 px-2 py-0.5 rounded">Tap to Call 📞</span>
                    </a>

                    <!-- Service, Budget & Meta -->
                    <div class="flex items-center justify-between text-xs text-slate-300 pt-1 border-t border-slate-900">
                        <div>
                            <span class="text-slate-400">Service:</span>
                            <span class="text-white font-medium ml-1">{{ $lead->service_needed ?: 'General' }}</span>
                        </div>
                        @if($lead->budget)
                            <div class="font-extrabold text-emerald-400 font-mono">${{ number_format($lead->budget, 0) }}</div>
                        @endif
                    </div>

                    @if(auth()->user()->isSuperAdmin())
                        <div class="pt-2 border-t border-slate-900 flex items-center justify-between text-xs">
                            <span class="text-slate-400">Assigned Rep:</span>
                            <form action="{{ route('leads.assign', $lead) }}" method="POST">
                                @csrf
                                <select name="assigned_to" onchange="this.form.submit()" class="px-2 py-1 rounded bg-slate-900 border border-slate-800 text-[11px] text-slate-300">
                                    <option value="">Unassigned</option>
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}" {{ $lead->assigned_to == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-900">
                        <a href="{{ route('leads.show', $lead) }}" class="col-span-2 py-2.5 px-3 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-500 text-white flex items-center justify-center gap-1.5 shadow-md shadow-brand-600/25 transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>Log Status</span>
                        </a>

                        <a href="{{ route('leads.edit', $lead) }}" class="py-2.5 px-3 rounded-xl text-xs font-semibold bg-slate-850 hover:bg-slate-800 text-slate-300 flex items-center justify-center border border-slate-800">
                            Edit
                        </a>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-slate-400 text-xs">No leads found in this view.</div>
            @endforelse
        </div>

        <!-- 💻 DESKTOP TABLE VIEW (Visible on screens >= 768px) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-[11px] uppercase tracking-wider text-slate-400 bg-slate-950/50 border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-5 font-semibold">Client & Company</th>
                        <th class="py-3.5 px-4 font-semibold">Contact Details</th>
                        <th class="py-3.5 px-4 font-semibold">Service & Budget</th>
                        <th class="py-3.5 px-4 font-semibold">Current Status</th>
                        @if(auth()->user()->isSuperAdmin())
                            <th class="py-3.5 px-4 font-semibold">Assigned Agent</th>
                        @endif
                        <th class="py-3.5 px-5 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($leads as $lead)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-4 px-5">
                                <a href="{{ route('leads.show', $lead) }}" class="font-bold text-white hover:text-brand-400 transition block">
                                    {{ $lead->client_name }}
                                </a>
                                @if($lead->company_name)
                                    <div class="text-xs text-slate-400">{{ $lead->company_name }}</div>
                                @endif
                                <div class="text-[11px] text-slate-500 mt-0.5">Source: {{ $lead->source }}</div>
                            </td>

                            <td class="py-4 px-4 font-mono text-xs text-slate-300">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <a href="tel:{{ $lead->phone }}" class="hover:underline">{{ $lead->phone }}</a>
                                </div>
                                @if($lead->email)
                                    <div class="text-[11px] text-slate-400 font-sans mt-0.5 truncate max-w-[180px]">{{ $lead->email }}</div>
                                @endif
                                @if($lead->country)
                                    <div class="text-[11px] text-slate-500 font-sans mt-0.5">{{ $lead->country }}</div>
                                @endif
                            </td>

                            <td class="py-4 px-4">
                                <div class="text-xs font-semibold text-slate-200">{{ $lead->service_needed ?: 'General Scope' }}</div>
                                @if($lead->budget)
                                    <div class="text-xs font-bold text-emerald-400 mt-0.5">${{ number_format($lead->budget, 2) }}</div>
                                @else
                                    <div class="text-[11px] text-slate-500">Budget not quoted</div>
                                @endif
                            </td>

                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider {{ $lead->status_badge }}">
                                    {{ str_replace('_', ' ', $lead->status) }}
                                </span>
                                @if($lead->last_contacted_at)
                                    <div class="text-[10px] text-slate-400 mt-1">Called {{ $lead->last_contacted_at->diffForHumans() }}</div>
                                @endif
                            </td>

                            @if(auth()->user()->isSuperAdmin())
                                <td class="py-4 px-4">
                                    <form action="{{ route('leads.assign', $lead) }}" method="POST" class="flex items-center gap-1.5">
                                        @csrf
                                        <select name="assigned_to" onchange="this.form.submit()" 
                                                class="px-2.5 py-1.5 rounded-lg text-xs bg-slate-950 border border-slate-800 text-slate-300 focus:outline-none focus:border-brand-500">
                                            <option value="">Unassigned</option>
                                            @foreach($agents as $agent)
                                                <option value="{{ $agent->id }}" {{ $lead->assigned_to == $agent->id ? 'selected' : '' }}>
                                                    {{ $agent->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                            @endif

                            <td class="py-4 px-5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('leads.show', $lead) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-brand-600 hover:bg-brand-500 text-white transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        <span>Call & Log</span>
                                    </a>

                                    <a href="{{ route('leads.edit', $lead) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>

                                    @if(auth()->user()->isSuperAdmin())
                                        <form action="{{ route('leads.destroy', $lead) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this lead?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isSuperAdmin() ? '6' : '5' }}" class="py-12 text-center text-slate-400">
                                <p class="text-sm">No leads match your criteria.</p>
                                <a href="{{ route('leads.create') }}" class="mt-3 inline-block text-xs font-semibold text-brand-400 hover:underline">
                                    + Add New Lead
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leads->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                {{ $leads->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
