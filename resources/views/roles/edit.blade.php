@extends('layouts.app')

@section('title', 'Edit Role: ' . $role->name)
@section('page_heading', 'Edit Role & Permissions')
@section('page_subheading', 'Customize access rights and permissions for ' . $role->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-8 shadow-2xl">
        <form action="{{ route('roles.update', $role) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Role Name *</label>
                    <input type="text" name="name" value="{{ old('name', $role->name) }}" required
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Role Description</label>
                    <input type="text" name="description" value="{{ old('description', $role->description) }}"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                </div>
            </div>

            @if($role->slug === 'super_admin')
                <div class="p-4 rounded-xl bg-purple-950/40 border border-purple-800/50 text-xs text-purple-300 flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span><strong>Super Admin</strong> retains unrestricted master access to every module and feature by default.</span>
                </div>
            @endif

            <!-- Permissions Category Grid -->
            <div class="pt-4 border-t border-slate-800 space-y-5">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Manage Granted Permissions
                </h3>

                <div class="space-y-4">
                    @foreach($permissionsGrouped as $groupName => $perms)
                        <div class="p-5 rounded-2xl bg-slate-950/70 border border-slate-800 space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-800/80">
                                <span class="text-xs font-black uppercase tracking-wider text-brand-300">{{ $groupName }}</span>
                                <span class="text-[11px] text-slate-500">{{ $perms->count() }} permissions in category</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 pt-1">
                                @foreach($perms as $perm)
                                    @php
                                        $isChecked = in_array($perm->id, old('permissions', $rolePermissionIds));
                                    @endphp
                                    <label class="p-3 rounded-xl bg-slate-900 border border-slate-800/80 hover:border-brand-500/50 cursor-pointer flex items-start gap-3 transition">
                                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                               {{ $isChecked ? 'checked' : '' }}
                                               class="w-4 h-4 rounded mt-0.5 bg-slate-950 border-slate-700 text-brand-600 focus:ring-brand-500">
                                        <div>
                                            <div class="text-xs font-bold text-white">{{ $perm->name }}</div>
                                            <div class="text-[10px] text-slate-400 mt-0.5 font-mono">{{ $perm->slug }}</div>
                                            @if($perm->description)
                                                <div class="text-[10px] text-slate-500 mt-0.5">{{ $perm->description }}</div>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <a href="{{ route('roles.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-400 hover:text-white">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-500 text-white shadow-lg shadow-brand-600/25 transition">
                    Save Changes &rarr;
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
