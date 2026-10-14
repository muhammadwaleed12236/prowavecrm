<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'View All Leads Pool', 'slug' => 'leads.view_all', 'group' => 'Leads', 'description' => 'Access all company leads pool across all reps'],
            ['name' => 'View My Assigned Leads', 'slug' => 'leads.view_assigned', 'group' => 'Leads', 'description' => 'View only prospects assigned to the logged-in agent'],
            ['name' => 'Create New Leads', 'slug' => 'leads.create', 'group' => 'Leads', 'description' => 'Add new leads to the pipeline'],
            ['name' => 'Edit Lead Details', 'slug' => 'leads.edit', 'group' => 'Leads', 'description' => 'Update contact and budget details of leads'],
            ['name' => 'Delete Leads', 'slug' => 'leads.delete', 'group' => 'Leads', 'description' => 'Permanently delete leads from CRM'],
            ['name' => 'Assign & Distribute Leads', 'slug' => 'leads.assign', 'group' => 'Leads', 'description' => 'Allocate leads to sales representatives'],
            ['name' => 'Bulk Import from Excel', 'slug' => 'leads.import', 'group' => 'Leads', 'description' => 'Upload Excel / CSV spreadsheets of contacts'],
            ['name' => 'Log Cold Calls & Status', 'slug' => 'calls.log', 'group' => 'Calls', 'description' => 'Log call remarks, duration and change pipeline status'],
            ['name' => 'View All Live Team Calls', 'slug' => 'calls.view_all', 'group' => 'Calls', 'description' => 'Audit stream of all calls across all representatives'],
            ['name' => 'Schedule & Update Meetings', 'slug' => 'meetings.manage', 'group' => 'Meetings', 'description' => 'Set up demo calls, meeting links and mark completed'],
            ['name' => 'View All Company Meetings', 'slug' => 'meetings.view_all', 'group' => 'Meetings', 'description' => 'View complete company calendar of discovery meetings'],
            ['name' => 'Close & Win Deals', 'slug' => 'deals.manage', 'group' => 'Deals', 'description' => 'Convert leads to won deals and record contract budgets'],
            ['name' => 'View All Won Revenue', 'slug' => 'deals.view_all', 'group' => 'Deals', 'description' => 'View total revenue stats, budgets and closed project scopes'],
            ['name' => 'View All Team Daily Reports', 'slug' => 'reports.view_all', 'group' => 'Reports', 'description' => 'Access daily automated activity reports for all reps'],
            ['name' => 'View Personal Daily Report', 'slug' => 'reports.view_own', 'group' => 'Reports', 'description' => 'Access personal daily call counts and conversion stats'],
            ['name' => 'Manage Team Representatives', 'slug' => 'team.manage', 'group' => 'Team', 'description' => 'Create sales agents, set passwords and toggle active status'],
            ['name' => 'Manage Roles & Permissions', 'slug' => 'roles.manage', 'group' => 'Administration', 'description' => 'Create roles and customize granted permissions'],
        ];

        $permissionModels = [];
        foreach ($permissions as $permission) {
            $permissionModels[$permission['slug']] = Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        $superAdminRole = Role::updateOrCreate(
            ['slug' => 'super_admin'],
            [
                'name' => 'Super Administrator',
                'description' => 'Full unrestricted access to all company leads, revenue, reports, and settings.',
            ]
        );
        $superAdminRole->permissions()->sync(collect($permissionModels)->pluck('id'));

        User::updateOrCreate(
            ['email' => 'admin@prowave.com'],
            [
                'name' => 'ProWave Admin',
                'password' => Hash::make('owner123'),
                'role' => 'super_admin',
                'role_id' => $superAdminRole->id,
                'status' => 'active',
            ]
        );
    }
}
