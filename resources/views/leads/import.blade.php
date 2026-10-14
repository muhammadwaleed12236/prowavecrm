@extends('layouts.app')

@section('title', 'Import Leads from Excel')
@section('page_heading', 'Import Leads from Excel / CSV')
@section('page_subheading', 'Bulk upload contacts, prospects and cold calling lists')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ assignmentMode: 'round_robin', fileName: '' }">

    <!-- Top Action Card with Template Download -->
    <div class="bg-gradient-to-r from-brand-950/60 via-slate-900/90 to-cyan-950/40 border border-brand-500/30 rounded-3xl p-6 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Download Excel / CSV Sample Template
            </h2>
            <p class="text-xs text-slate-400 mt-1">Use our pre-formatted template with standard headers for quick error-free bulk upload.</p>
        </div>

        <a href="{{ route('leads.import.template') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/25 transition whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            <span>Download Sample CSV Template</span>
        </a>
    </div>

    <!-- Main Import Form -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-8 shadow-2xl">
        <form action="{{ route('leads.import.process') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Upload Box -->
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Select Spreadsheet File (.xlsx, .xls, .csv) *</label>
                
                <div class="relative border-2 border-dashed border-slate-700 hover:border-brand-500 rounded-2xl p-8 text-center bg-slate-950/60 transition group cursor-pointer">
                    <input type="file" name="file" required accept=".xlsx,.xls,.csv,.txt"
                           @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

                    <div class="space-y-2">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-brand-500/10 text-brand-400 flex items-center justify-center group-hover:scale-110 transition">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        <div class="text-sm font-bold text-white" x-text="fileName ? fileName : 'Click to browse or drag Excel / CSV file here'"></div>
                        <p class="text-xs text-slate-400">Supported formats: Microsoft Excel (.xlsx, .xls) and CSV (.csv) up to 10MB</p>
                    </div>
                </div>
            </div>

            <!-- Super Admin Lead Assignment Rules -->
            @if(auth()->user()->isSuperAdmin())
                <div class="p-5 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-4">
                    <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                        Assignment Strategy for Imported Leads
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <!-- Option 1: Round Robin -->
                        <label class="p-3.5 rounded-xl border cursor-pointer transition flex items-start gap-3"
                               :class="assignmentMode === 'round_robin' ? 'bg-brand-500/10 border-brand-500 text-white' : 'bg-slate-900 border-slate-800 text-slate-300 hover:border-slate-700'">
                            <input type="radio" name="assignment_mode" value="round_robin" x-model="assignmentMode" class="mt-0.5 text-brand-600 focus:ring-brand-500">
                            <div>
                                <div class="text-xs font-bold">Round-Robin Equal Split</div>
                                <p class="text-[11px] text-slate-400 mt-0.5">Distributes leads equally among all active sales agents.</p>
                            </div>
                        </label>

                        <!-- Option 2: Specific Agent -->
                        <label class="p-3.5 rounded-xl border cursor-pointer transition flex items-start gap-3"
                               :class="assignmentMode === 'specific' ? 'bg-brand-500/10 border-brand-500 text-white' : 'bg-slate-900 border-slate-800 text-slate-300 hover:border-slate-700'">
                            <input type="radio" name="assignment_mode" value="specific" x-model="assignmentMode" class="mt-0.5 text-brand-600 focus:ring-brand-500">
                            <div>
                                <div class="text-xs font-bold">Assign to Specific Rep</div>
                                <p class="text-[11px] text-slate-400 mt-0.5">Allocate all imported leads to one selected sales rep.</p>
                            </div>
                        </label>

                        <!-- Option 3: Unassigned -->
                        <label class="p-3.5 rounded-xl border cursor-pointer transition flex items-start gap-3"
                               :class="assignmentMode === 'unassigned' ? 'bg-brand-500/10 border-brand-500 text-white' : 'bg-slate-900 border-slate-800 text-slate-300 hover:border-slate-700'">
                            <input type="radio" name="assignment_mode" value="unassigned" x-model="assignmentMode" class="mt-0.5 text-brand-600 focus:ring-brand-500">
                            <div>
                                <div class="text-xs font-bold">Leave in Pool (Unassigned)</div>
                                <p class="text-[11px] text-slate-400 mt-0.5">Keep in unassigned leads pool for manual assignment later.</p>
                            </div>
                        </label>
                    </div>

                    <!-- Rep select dropdown when 'specific' is active -->
                    <div x-show="assignmentMode === 'specific'" x-cloak class="pt-2">
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Select Sales Representative *</label>
                        <select name="assigned_to" class="w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-white text-xs focus:outline-none focus:border-brand-500">
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->name }} ({{ $agent->email }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @else
                <div class="p-4 rounded-xl bg-cyan-950/30 border border-cyan-800/40 text-xs text-cyan-300 flex items-center gap-2">
                    <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>All imported leads will automatically be added to your personal cold calling queue.</span>
                </div>
            @endif

            <!-- Supported Columns Guide -->
            <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Recognized Spreadsheet Columns:</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 text-xs text-slate-300">
                    <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                        <span class="text-purple-400 font-bold block">Lead Type</span>
                        <span class="text-[11px] text-slate-400">Values: <code class="text-white">own</code> or <code class="text-white">outsource</code> (defaults to own)</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                        <span class="text-brand-400 font-bold block">Client / Business Name *</span>
                        <span class="text-[11px] text-slate-400">or Name, Full Name, Company</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                        <span class="text-brand-400 font-bold block">Phone *</span>
                        <span class="text-[11px] text-slate-400">or Mobile, Contact, Phone No</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                        <span class="text-slate-300 font-semibold block">Email (Optional)</span>
                        <span class="text-[11px] text-slate-400">or Email Address, Mail</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 sm:col-span-2">
                        <span class="text-slate-300 font-semibold block">Remarks (Optional)</span>
                        <span class="text-[11px] text-slate-400">or Notes, Comments, Description</span>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <a href="{{ route('leads.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-400 hover:text-white">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-500 text-white shadow-lg shadow-brand-600/30 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Start Bulk Import &rarr;</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
