<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount(['permissions', 'users'])->get();
        $permissionsGrouped = Permission::all()->groupBy('group');

        return view('roles.index', compact('roles', 'permissionsGrouped'));
    }

    public function create()
    {
        $permissionsGrouped = Permission::all()->groupBy('group');

        return view('roles.create', compact('permissionsGrouped'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name'], '_'),
            'description' => $validated['description'] ?? null,
        ]);

        if (!empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return redirect()->route('roles.index')->with('success', "Role '{$role->name}' created successfully!");
    }

    public function edit(Role $role)
    {
        $permissionsGrouped = Permission::all()->groupBy('group');
        $rolePermissionIds = $role->permissions->pluck('id')->toArray();

        return view('roles.edit', compact('role', 'permissionsGrouped', 'rolePermissionIds'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // If editing system super_admin, keep slug
        $role->update([
            'name' => $validated['name'],
            'slug' => $role->slug === 'super_admin' ? 'super_admin' : Str::slug($validated['name'], '_'),
            'description' => $validated['description'] ?? null,
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', "Role '{$role->name}' permissions updated successfully!");
    }

    public function destroy(Role $role)
    {
        if (in_array($role->slug, ['super_admin', 'sales_agent'])) {
            return back()->withErrors(['error' => 'Default system roles cannot be deleted.']);
        }

        if ($role->users()->count() > 0) {
            return back()->withErrors(['error' => "Cannot delete role '{$role->name}' because {$role->users()->count()} user(s) are currently assigned to it."]);
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', "Role deleted successfully.");
    }
}
