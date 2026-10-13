@extends('layouts.app')

@section('title', 'Roles & Permissions Management')
@section('page_heading', 'User Roles & Permissions')
@section('page_subheading', 'Define custom roles, assign granular permissions and manage user access control')

@section('content')
<div class="space-y-6">

    <!-- Top Action Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl">
        <div>
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Role-Based Access Control (RBAC)
            </h3>
            <p class="text-xs text-slate-400 mt-0.5">Control what each sales executive, manager, or closer can see and perform.</p>
        </div>

        <a href="{{ route('roles.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-500 text-white shadow-lg shadow-brand-600/25 transition whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Create New Role</span>
        </a>
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($roles as $role)
            <div class="p-6 rounded-2xl bg-slate-900/90 border border-slate-800 hover:border-slate-700 transition flex flex-col justify-between shadow-xl">
                <div>
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider
                            @if($role->slug === 'super_admin') bg-purple-500/15 text-purple-300 border border-purple-500/30
                            @elseif($role->slug === 'sales_manager') bg-indigo-500/15 text-indigo-300 border border-indigo-500/30
                            @elseif($role->slug === 'sales_agent') bg-cyan-500/15 text-cyan-300 border border-cyan-500/30
                            @else bg-emerald-500/15 text-emerald-300 border border-emerald-500/30
                            @endif">
                            {{ $role->slug }}
                        </span>

                        <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-slate-950 text-slate-300 border border-slate-800">
                            {{ $role->users_count }} users assigned
                        </span>
                    </div>

                    <h3 class="text-lg font-black text-white mb-1">{{ $role->name }}</h3>
                    <p class="text-xs text-slate-400 mb-4 line-clamp-2">{{ $role->description ?: 'Custom assigned permissions role.' }}</p>

                    <!-- Permissions Count & Sample Tags -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs font-semibold">
                            <span class="text-slate-400">Granted Permissions:</span>
                            <span class="text-brand-300 font-mono">{{ $role->slug === 'super_admin' ? 'ALL (Unlimited)' : $role->permissions_count . ' Active' }}</span>
                        </div>

                        <div class="flex flex-wrap gap-1 max-h-24 overflow-y-auto pt-1">
                            @if($role->slug === 'super_admin')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-950/60 text-purple-300 border border-purple-800/40">Full Super Administrator Master Access</span>
                            @else
                                @forelse($role->permissions->take(6) as $perm)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-950 text-slate-300 border border-slate-800">
                                        {{ $perm->name }}
                                    </span>
                                @empty
                                    <span class="text-[11px] text-slate-500 italic">No permissions assigned yet</span>
                                @endforelse
                                @if($role->permissions->count() > 6)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] text-slate-500">+{{ $role->permissions->count() - 6 }} more</span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="mt-5 pt-3 border-t border-slate-800 flex items-center justify-between">
                    <a href="{{ route('roles.edit', $role) }}" class="text-xs font-bold text-brand-400 hover:text-brand-300 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>Edit Permissions &rarr;</span>
                    </a>

                    @if(!in_array($role->slug, ['super_admin', 'sales_agent']))
                        <form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this custom role?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 hover:underline">
                                Delete
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
