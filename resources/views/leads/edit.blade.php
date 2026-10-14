@extends('layouts.app')

@section('title', 'Edit Lead: ' . $lead->client_name)
@section('page_heading', 'Edit Lead')
@section('page_subheading', 'Update lead details for ' . $lead->client_name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl">
        <form action="{{ route('leads.update', $lead) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Lead Type Selector -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2.5">Lead Type *</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="relative flex items-center gap-3 p-3.5 rounded-xl border cursor-pointer transition select-none
                                  {{ old('lead_type', $lead->lead_type) === 'own' ? 'bg-brand-500/10 border-brand-500 text-white shadow-sm' : 'bg-slate-950 border-slate-800 text-slate-400 hover:border-slate-700' }}">
                        <input type="radio" name="lead_type" value="own" {{ old('lead_type', $lead->lead_type) === 'own' ? 'checked' : '' }}
                               class="text-brand-600 focus:ring-brand-500 bg-slate-900 border-slate-700">
                        <div>
                            <span class="block text-sm font-bold text-white">Own Lead</span>
                            <span class="block text-[11px] text-slate-400">Direct ProWave Prospect</span>
                        </div>
                    </label>

                    <label class="relative flex items-center gap-3 p-3.5 rounded-xl border cursor-pointer transition select-none
                                  {{ old('lead_type', $lead->lead_type) === 'outsource' ? 'bg-purple-500/10 border-purple-500 text-white shadow-sm' : 'bg-slate-950 border-slate-800 text-slate-400 hover:border-slate-700' }}">
                        <input type="radio" name="lead_type" value="outsource" {{ old('lead_type', $lead->lead_type) === 'outsource' ? 'checked' : '' }}
                               class="text-purple-600 focus:ring-purple-500 bg-slate-900 border-slate-700">
                        <div>
                            <span class="block text-sm font-bold text-white">Outsource</span>
                            <span class="block text-[11px] text-slate-400">Partner / External Source</span>
                        </div>
                    </label>
                </div>
                @error('lead_type')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Field 1: Client Name & Business Name -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Client Name / Business Name *</label>
                <input type="text" name="client_name" value="{{ old('client_name', $lead->client_name) }}" required
                       placeholder="e.g. John Doe / Acme Logistics"
                       class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                @error('client_name')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Field 2: Phone Number -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Phone Number *</label>
                <input type="text" name="phone" value="{{ old('phone', $lead->phone) }}" required
                       placeholder="e.g. +1 (555) 019-2834 or 03001234567"
                       class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                @error('phone')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Field 3: Email (Optional) -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Email Address</label>
                    <span class="text-[11px] text-slate-500 font-medium">Optional</span>
                </div>
                <input type="email" name="email" value="{{ old('email', $lead->email) }}"
                       placeholder="e.g. client@example.com"
                       class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                @error('email')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Pipeline Status -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Current Pipeline Status *</label>
                <select name="status" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                    <option value="new" {{ old('status', $lead->status) === 'new' ? 'selected' : '' }}>New</option>
                    <option value="contacted" {{ old('status', $lead->status) === 'contacted' ? 'selected' : '' }}>Contacted</option>
                    <option value="interested" {{ old('status', $lead->status) === 'interested' ? 'selected' : '' }}>Interested</option>
                    <option value="callback" {{ old('status', $lead->status) === 'callback' ? 'selected' : '' }}>Callback</option>
                    <option value="meeting_scheduled" {{ old('status', $lead->status) === 'meeting_scheduled' ? 'selected' : '' }}>Meeting Scheduled</option>
                    <option value="proposal_sent" {{ old('status', $lead->status) === 'proposal_sent' ? 'selected' : '' }}>Proposal Sent</option>
                    <option value="deal_won" {{ old('status', $lead->status) === 'deal_won' ? 'selected' : '' }}>Deal Won</option>
                    <option value="lost" {{ old('status', $lead->status) === 'lost' ? 'selected' : '' }}>Lost</option>
                    <option value="not_interested" {{ old('status', $lead->status) === 'not_interested' ? 'selected' : '' }}>Not Interested</option>
                </select>
                @error('status')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Field 4: Remarks (Optional) -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Remarks / Notes</label>
                    <span class="text-[11px] text-slate-500 font-medium">Optional</span>
                </div>
                <textarea name="notes" rows="3"
                          placeholder="Conversation remarks or background details..."
                          class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">{{ old('notes', $lead->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Super Admin: Assigned Representative -->
            @if(auth()->user()->isSuperAdmin())
                <div class="pt-2 border-t border-slate-800">
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Assigned Sales Representative</label>
                    <select name="assigned_to" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                        <option value="">-- Unassigned (Pool) --</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ old('assigned_to', $lead->assigned_to) == $agent->id ? 'selected' : '' }}>
                                {{ $agent->name }} ({{ $agent->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <a href="{{ route('leads.show', $lead) }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-400 hover:text-white">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-3 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-500 text-white shadow-lg shadow-brand-600/25 transition">
                    Update Lead &rarr;
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
