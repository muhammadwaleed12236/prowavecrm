@extends('layouts.app')

@section('title', 'Sales Team & Representatives')
@section('page_heading', 'Team & Sales Reps')
@section('page_subheading', 'Manage ProWave sales executives, assign roles and monitor credentials')

@section('content')
<div class="space-y-6" x-data="{ addModalOpen: false }">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl">
        <div>
            <h3 class="text-base font-bold text-white">Active Workforce ({{ $members->count() }})</h3>
            <p class="text-xs text-slate-400">Team members, designations and access privileges</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('roles.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition">
                <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span>Roles & Permissions HQ</span>
            </a>

            <button @click="addModalOpen = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-500 text-white shadow-lg shadow-brand-600/25 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add New Member</span>
            </button>
        </div>
    </div>

    <!-- Team Members Table -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-[11px] uppercase tracking-wider text-slate-400 bg-slate-950/40 border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-6 font-semibold">User Details</th>
                        <th class="py-3.5 px-4 font-semibold">Assigned Role</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Assigned Leads</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Calls Made</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Meetings</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Won Revenue</th>
                        <th class="py-3.5 px-6 font-semibold text-right">Account Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach($members as $member)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-xl {{ $member->isSuperAdmin() ? 'bg-purple-500/20 text-purple-300' : 'bg-brand-500/20 text-brand-300' }} font-bold flex items-center justify-center text-xs">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-white">{{ $member->name }}</div>
                                        <div class="text-xs text-slate-400 font-mono">{{ $member->email }}</div>
                                        @if($member->phone)
                                            <div class="text-[11px] text-slate-500 font-mono">{{ $member->phone }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="py-4 px-4">
                                @if($member->id === auth()->id() && $member->isSuperAdmin())
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider bg-purple-500/15 text-purple-300 border border-purple-500/30">
                                        Super Admin
                                    </span>
                                @else
                                    <form action="{{ route('team.update-role', $member) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <select name="role_id" onchange="this.form.submit()"
                                                class="px-2.5 py-1.5 rounded-lg text-xs bg-slate-950 border border-slate-800 text-slate-200 focus:outline-none focus:border-brand-500">
                                            @foreach($roles as $roleOption)
                                                <option value="{{ $roleOption->id }}" {{ ($member->role_id == $roleOption->id || $member->role == $roleOption->slug) ? 'selected' : '' }}>
                                                    {{ $roleOption->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                @endif
                            </td>

                            <td class="py-4 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-800 text-slate-200">
                                    {{ $member->leads_count }} leads
                                </span>
                            </td>

                            <td class="py-4 px-4 text-center font-semibold text-slate-300">
                                {{ $member->call_logs_count }} calls
                            </td>

                            <td class="py-4 px-4 text-center font-semibold text-purple-400">
                                {{ $member->meetings_count }}
                            </td>

                            <td class="py-4 px-4 text-right font-extrabold text-emerald-400 font-mono">
                                ${{ number_format($member->deals_sum_deal_amount ?? 0, 2) }}
                            </td>

                            <td class="py-4 px-6 text-right">
                                @if($member->id !== auth()->id())
                                    <form action="{{ route('team.toggle-status', $member) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-3 py-1 rounded-lg text-xs font-bold transition {{ $member->status === 'active' ? 'bg-emerald-500/15 text-emerald-400 hover:bg-emerald-500/25 border border-emerald-500/30' : 'bg-rose-500/15 text-rose-400 hover:bg-rose-500/25 border border-rose-500/30' }}">
                                            {{ ucfirst($member->status) }}
                                        </button>
                                    </form>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-800 text-slate-400">Active (You)</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Sales Rep Modal -->
    <div x-show="addModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.away="addModalOpen = false" class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl relative">
            <h3 class="text-lg font-bold text-white mb-1">Add Team Member</h3>
            <p class="text-xs text-slate-400 mb-5">Create a login account and assign a designated role</p>

            <form action="{{ route('team.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Full Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Hamza Siddiqui"
                           class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Email Address *</label>
                    <input type="email" name="email" required placeholder="e.g. hamza@prowave.com"
                           class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Password *</label>
                    <input type="password" name="password" required placeholder="Minimum 6 characters"
                           class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Phone Number</label>
                    <input type="text" name="phone" placeholder="e.g. +92 300 0000000"
                           class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Designation / Role *</label>
                    <select name="role_id" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:outline-none focus:border-brand-500">
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}" {{ $r->slug === 'sales_agent' ? 'selected' : '' }}>
                                {{ $r->name }} ({{ $r->description ?: $r->slug }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-400 hover:text-white">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-500 text-white shadow-lg shadow-brand-600/25 transition">
                        Create Account &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
