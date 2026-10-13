@extends('layouts.app')

@section('title', 'Edit Lead: ' . $lead->client_name)
@section('page_heading', 'Edit Lead')
@section('page_subheading', 'Update lead details for ' . $lead->client_name)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-8 shadow-2xl">
        <form action="{{ route('leads.update', $lead) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Client Full Name *</label>
                    <input type="text" name="client_name" value="{{ old('client_name', $lead->client_name) }}" required
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Company / Business Name</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $lead->company_name) }}"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Phone Number *</label>
                    <input type="text" name="phone" value="{{ old('phone', $lead->phone) }}" required
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $lead->email) }}"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Country / Region</label>
                    <input type="text" name="country" value="{{ old('country', $lead->country) }}"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Current Pipeline Status</label>
                    <select name="status" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
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
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Service / Product Needed</label>
                    <input type="text" name="service_needed" value="{{ old('service_needed', $lead->service_needed) }}"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Estimated Budget ($ USD)</label>
                    <input type="number" step="0.01" name="budget" value="{{ old('budget', $lead->budget) }}"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Lead Source</label>
                    <input type="text" name="source" value="{{ old('source', $lead->source) }}"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                </div>

                @if(auth()->user()->isSuperAdmin())
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Assigned Sales Representative</label>
                        <select name="assigned_to" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                            <option value="">-- Unassigned --</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ old('assigned_to', $lead->assigned_to) == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Notes</label>
                <textarea name="notes" rows="3" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">{{ old('notes', $lead->notes) }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <a href="{{ route('leads.show', $lead) }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-400 hover:text-white">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-500 text-white shadow-lg shadow-brand-600/25 transition">
                    Update Lead &rarr;
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
