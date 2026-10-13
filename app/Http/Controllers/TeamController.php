<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class TeamController extends Controller
{
    public function index()
    {
        $members = User::with(['roleModel'])
            ->withCount(['leads', 'callLogs', 'meetings', 'deals'])
            ->withSum('deals', 'deal_amount')
            ->orderBy('id', 'asc')
            ->get();

        $roles = Role::all();

        return view('team.index', compact('members', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:50',
        ]);

        $role = Role::findOrFail($validated['role_id']);

        $validated['role'] = $role->slug;
        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = 'active';

        User::create($validated);

        return back()->with('success', "Team member '{$validated['name']}' added with role '{$role->name}' successfully!");
    }

    public function updateRole(Request $request, User $user)
    {
        if ($user->id === auth()->id() && $user->isSuperAdmin()) {
            return back()->withErrors(['error' => 'You cannot change your own Super Admin role.']);
        }

        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($validated['role_id']);

        $user->update([
            'role_id' => $role->id,
            'role' => $role->slug,
        ]);

        return back()->with('success', "Updated role for '{$user->name}' to '{$role->name}'.");
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot deactivate your own account.']);
        }

        $user->update([
            'status' => $user->status === 'active' ? 'inactive' : 'active',
        ]);

        $statusText = ucfirst($user->status);
        return back()->with('success', "User '{$user->name}' is now {$statusText}.");
    }
}
