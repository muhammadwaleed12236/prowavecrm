@extends('layouts.app')

@section('title', 'Add New Lead')
@section('page_heading', 'Add New Lead')
@section('page_subheading', 'Create a new prospect into ProWave sales pipeline')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-8 shadow-2xl">
        <form action="{{ route('leads.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Client Full Name *</label>
                    <input type="text" name="client_name" value="{{ old('client_name') }}" required placeholder="e.g. John Doe"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Company / Business Name</label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="e.g. Acme Corp"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Phone Number *</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="e.g. +1 (555) 019-2834"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="e.g. john@example.com"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Country / Region</label>
                    <input type="text" name="country" value="{{ old('country', 'USA') }}" placeholder="e.g. USA, UK, UAE"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Lead Source</label>
                    <select name="source" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                        <option value="Cold Calling">Cold Calling</option>
                        <option value="LinkedIn Outreach">LinkedIn Outreach</option>
                        <option value="Website Inbound">Website Inbound</option>
                        <option value="Upwork / Freelance">Upwork / Freelance</option>
                        <option value="Referral">Client Referral</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Service / Product Needed</label>
                    <input type="text" name="service_needed" value="{{ old('service_needed') }}" placeholder="e.g. Custom Web App, Mobile App, SEO"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Estimated Budget ($ USD)</label>
                    <input type="number" step="0.01" name="budget" value="{{ old('budget') }}" placeholder="e.g. 3500.00"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                </div>

                @if(auth()->user()->isSuperAdmin())
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Assign to Sales Representative</label>
                        <select name="assigned_to" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                            <option value="">-- Leave Unassigned --</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ old('assigned_to') == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }} ({{ $agent->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Initial Notes / Requirements</label>
                <textarea name="notes" rows="3" placeholder="Key talking points or background info..."
                          class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <a href="{{ route('leads.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-400 hover:text-white">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-500 text-white shadow-lg shadow-brand-600/25 transition">
                    Save & Create Lead &rarr;
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
