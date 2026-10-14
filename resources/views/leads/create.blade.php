@extends('layouts.app')

@section('title', 'Quick Bulk Lead Entry')
@section('page_heading', 'Add Leads (Bulk Entry)')
@section('page_subheading', 'Enter multiple leads quickly. Type information and press Enter to automatically open new rows.')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="bulkLeadManager()">

    <!-- Form Container -->
    <form action="{{ route('leads.store') }}" method="POST" @submit="handleSubmit($event)">
        @csrf

        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-5 sm:p-7 shadow-2xl space-y-6 glass-card">
            
            <!-- Top Configuration Bar: Lead Type & Super Admin Assignment -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-center pb-6 border-b border-slate-800/80">
                
                <!-- Lead Type Selector (Select once for entire batch) -->
                <div class="md:col-span-7 space-y-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                        Select Lead Type (Applied to all rows) *
                    </label>
                    <div class="grid grid-cols-2 gap-3 max-w-md">
                        <!-- Option 1: Own Lead -->
                        <label class="relative flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition select-none"
                               :class="leadType === 'own' ? 'bg-brand-500/15 border-brand-500 text-white shadow-sm ring-1 ring-brand-500/40' : 'bg-slate-950/80 border-slate-800 text-slate-400 hover:border-slate-700'">
                            <input type="radio" name="lead_type" value="own" x-model="leadType" class="text-brand-600 focus:ring-brand-500 bg-slate-900 border-slate-700">
                            <div>
                                <span class="block text-xs sm:text-sm font-bold text-white">Own Lead</span>
                                <span class="block text-[10px] sm:text-[11px] text-slate-400">Direct Prospect</span>
                            </div>
                        </label>

                        <!-- Option 2: Outsource Lead -->
                        <label class="relative flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition select-none"
                               :class="leadType === 'outsource' ? 'bg-purple-500/15 border-purple-500 text-white shadow-sm ring-1 ring-purple-500/40' : 'bg-slate-950/80 border-slate-800 text-slate-400 hover:border-slate-700'">
                            <input type="radio" name="lead_type" value="outsource" x-model="leadType" class="text-purple-600 focus:ring-purple-500 bg-slate-900 border-slate-700">
                            <div>
                                <span class="block text-xs sm:text-sm font-bold text-white">Outsource</span>
                                <span class="block text-[10px] sm:text-[11px] text-slate-400">External / Partner</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Super Admin: Assign entire batch to Rep -->
                @if(auth()->user()->isSuperAdmin())
                    <div class="md:col-span-5 space-y-2">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            Assign Batch To Sales Rep
                        </label>
                        <select name="assigned_to" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs sm:text-sm focus:outline-none focus:border-brand-500">
                            <option value="">-- Leave In Pool (Unassigned) --</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ old('assigned_to') == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }} ({{ $agent->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            <!-- Header Instruction Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs bg-slate-950/60 p-3 rounded-xl border border-slate-800/80">
                <div class="flex items-center gap-2 text-slate-300">
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-brand-500/20 text-brand-400 font-bold text-[11px]">i</span>
                    <span>Fill in prospect details. Press <kbd class="px-1.5 py-0.5 rounded bg-slate-800 border border-slate-700 text-white font-mono text-[10px]">Enter ↵</kbd> on any row to automatically append a new row.</span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="addRows(5)" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition">
                        + Add 5 Rows
                    </button>
                    <button type="button" @click="addRow()" class="px-3 py-1.5 rounded-lg text-[11px] font-bold bg-brand-600/30 hover:bg-brand-600/40 text-brand-300 border border-brand-500/40 transition">
                        + Add 1 Row
                    </button>
                </div>
            </div>

            <!-- Spreadsheet Rows Table -->
            <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-950/40">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-950 text-slate-400 uppercase tracking-wider text-[10px] font-bold border-b border-slate-800 select-none">
                        <tr>
                            <th class="py-3 px-3 text-center w-10">#</th>
                            <th class="py-3 px-3 min-w-[200px]">Client Name / Business Name *</th>
                            <th class="py-3 px-3 min-w-[160px]">Phone Number *</th>
                            <th class="py-3 px-3 min-w-[170px]">Email (Optional)</th>
                            <th class="py-3 px-3 min-w-[200px]">Remarks / Notes (Optional)</th>
                            <th class="py-3 px-2 text-center w-12">Del</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <template x-for="(row, index) in rows" :key="row.id">
                            <tr class="hover:bg-slate-800/30 transition group">
                                <!-- Row Number -->
                                <td class="py-2.5 px-3 text-center text-slate-500 font-mono text-[11px]" x-text="index + 1"></td>

                                <!-- 1. Client & Business Name -->
                                <td class="py-2 px-3">
                                    <input type="text" 
                                           :name="'leads[' + index + '][client_name]'" 
                                           x-model="row.client_name"
                                           placeholder="e.g. John Doe / Apex Logistics"
                                           @keydown.enter.prevent="handleEnter(index, 'client_name')"
                                           :id="'client_name_' + index"
                                           class="w-full px-3 py-2 bg-slate-900/90 border border-slate-800 rounded-lg text-white text-xs placeholder:text-slate-600 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                                </td>

                                <!-- 2. Phone Number -->
                                <td class="py-2 px-3">
                                    <input type="text" 
                                           :name="'leads[' + index + '][phone]'" 
                                           x-model="row.phone"
                                           placeholder="e.g. +1 555 0192 / 0300123"
                                           @keydown.enter.prevent="handleEnter(index, 'phone')"
                                           :id="'phone_' + index"
                                           class="w-full px-3 py-2 bg-slate-900/90 border border-slate-800 rounded-lg text-white font-mono text-xs placeholder:text-slate-600 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                                </td>

                                <!-- 3. Email Address -->
                                <td class="py-2 px-3">
                                    <input type="email" 
                                           :name="'leads[' + index + '][email]'" 
                                           x-model="row.email"
                                           placeholder="client@example.com"
                                           @keydown.enter.prevent="handleEnter(index, 'email')"
                                           :id="'email_' + index"
                                           class="w-full px-3 py-2 bg-slate-900/90 border border-slate-800 rounded-lg text-white text-xs placeholder:text-slate-600 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                                </td>

                                <!-- 4. Remarks -->
                                <td class="py-2 px-3">
                                    <input type="text" 
                                           :name="'leads[' + index + '][notes]'" 
                                           x-model="row.notes"
                                           placeholder="Requirements or remarks..."
                                           @keydown.enter.prevent="handleEnter(index, 'notes')"
                                           :id="'notes_' + index"
                                           class="w-full px-3 py-2 bg-slate-900/90 border border-slate-800 rounded-lg text-white text-xs placeholder:text-slate-600 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                                </td>

                                <!-- Delete Row Button -->
                                <td class="py-2 px-2 text-center">
                                    <button type="button" 
                                            @click="removeRow(index)" 
                                            :disabled="rows.length <= 1"
                                            title="Delete row"
                                            class="p-1 rounded text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 disabled:opacity-30 disabled:pointer-events-none transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Bottom Floating Action Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-3 border-t border-slate-800/80">
                <div class="flex items-center gap-3">
                    <button type="button" @click="addRow()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 transition">
                        <svg class="w-4 h-4 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Add Row</span>
                    </button>
                    <span class="text-xs text-slate-400">
                        Total Rows: <strong class="text-white" x-text="rows.length"></strong> |
                        Active Leads to Save: <strong class="text-emerald-400" x-text="validCount()"></strong>
                    </span>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('leads.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-400 hover:text-white transition">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-500 text-white shadow-lg shadow-brand-600/30 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Save All Leads &rarr;</span>
                    </button>
                </div>
            </div>

        </div>
    </form>

</div>

@push('scripts')
<script>
    function bulkLeadManager() {
        return {
            leadType: '{{ old('lead_type', 'own') }}',
            rows: [],
            
            init() {
                // Initialize with 10 default rows
                for (let i = 0; i < 10; i++) {
                    this.rows.push({
                        id: Date.now() + i,
                        client_name: '',
                        phone: '',
                        email: '',
                        notes: ''
                    });
                }
            },

            addRow(focusIndex = null, focusField = 'client_name') {
                const newId = Date.now() + Math.random();
                this.rows.push({
                    id: newId,
                    client_name: '',
                    phone: '',
                    email: '',
                    notes: ''
                });

                if (focusIndex !== null) {
                    this.$nextTick(() => {
                        const nextInput = document.getElementById(focusField + '_' + focusIndex);
                        if (nextInput) {
                            nextInput.focus();
                        }
                    });
                }
            },

            addRows(count = 5) {
                for (let i = 0; i < count; i++) {
                    this.rows.push({
                        id: Date.now() + Math.random() + i,
                        client_name: '',
                        phone: '',
                        email: '',
                        notes: ''
                    });
                }
            },

            removeRow(index) {
                if (this.rows.length > 1) {
                    this.rows.splice(index, 1);
                }
            },

            handleEnter(currentIndex, currentField) {
                // If pressing enter on the last row, automatically append a new row and focus it
                if (currentIndex === this.rows.length - 1) {
                    this.addRow(currentIndex + 1, currentField);
                } else {
                    // Focus next row's same field or first field
                    const nextInput = document.getElementById(currentField + '_' + (currentIndex + 1));
                    if (nextInput) {
                        nextInput.focus();
                    } else {
                        this.addRow(currentIndex + 1, currentField);
                    }
                }
            },

            validCount() {
                return this.rows.filter(r => (r.client_name && r.client_name.trim().length > 0) || (r.phone && r.phone.trim().length > 0)).length;
            },

            handleSubmit(e) {
                const count = this.validCount();
                if (count === 0) {
                    e.preventDefault();
                    alert('Please enter at least one prospect with a Client Name or Phone Number.');
                }
            }
        };
    }
</script>
@endpush
@endsection
