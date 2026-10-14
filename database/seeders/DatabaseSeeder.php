<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Lead;
use App\Models\CallLog;
use App\Models\Meeting;
use App\Models\Deal;
use App\Models\DailyReport;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Granular Permissions
        $permissions = [
            // Leads Management
            ['name' => 'View All Leads Pool', 'slug' => 'leads.view_all', 'group' => 'Leads', 'description' => 'Access all company leads pool across all reps'],
            ['name' => 'View My Assigned Leads', 'slug' => 'leads.view_assigned', 'group' => 'Leads', 'description' => 'View only prospects assigned to the logged-in agent'],
            ['name' => 'Create New Leads', 'slug' => 'leads.create', 'group' => 'Leads', 'description' => 'Add new leads to the pipeline'],
            ['name' => 'Edit Lead Details', 'slug' => 'leads.edit', 'group' => 'Leads', 'description' => 'Update contact and budget details of leads'],
            ['name' => 'Delete Leads', 'slug' => 'leads.delete', 'group' => 'Leads', 'description' => 'Permanently delete leads from CRM'],
            ['name' => 'Assign & Distribute Leads', 'slug' => 'leads.assign', 'group' => 'Leads', 'description' => 'Allocate leads to sales representatives'],
            ['name' => 'Bulk Import from Excel', 'slug' => 'leads.import', 'group' => 'Leads', 'description' => 'Upload Excel / CSV spreadsheets of contacts'],

            // Cold Calling
            ['name' => 'Log Cold Calls & Status', 'slug' => 'calls.log', 'group' => 'Calls', 'description' => 'Log call remarks, duration and change pipeline status'],
            ['name' => 'View All Live Team Calls', 'slug' => 'calls.view_all', 'group' => 'Calls', 'description' => 'Audit stream of all calls across all representatives'],

            // Meetings
            ['name' => 'Schedule & Update Meetings', 'slug' => 'meetings.manage', 'group' => 'Meetings', 'description' => 'Set up demo calls, meeting links and mark completed'],
            ['name' => 'View All Company Meetings', 'slug' => 'meetings.view_all', 'group' => 'Meetings', 'description' => 'View complete company calendar of discovery meetings'],

            // Deals & Revenue
            ['name' => 'Close & Win Deals', 'slug' => 'deals.manage', 'group' => 'Deals', 'description' => 'Convert leads to won deals and record contract budgets'],
            ['name' => 'View All Won Revenue', 'slug' => 'deals.view_all', 'group' => 'Deals', 'description' => 'View total revenue stats, budgets and closed project scopes'],

            // Daily Reports
            ['name' => 'View All Team Daily Reports', 'slug' => 'reports.view_all', 'group' => 'Reports', 'description' => 'Access daily automated activity reports for all reps'],
            ['name' => 'View Personal Daily Report', 'slug' => 'reports.view_own', 'group' => 'Reports', 'description' => 'Access personal daily call counts and conversion stats'],

            // Administration
            ['name' => 'Manage Team Representatives', 'slug' => 'team.manage', 'group' => 'Team', 'description' => 'Create sales agents, set passwords and toggle active status'],
            ['name' => 'Manage Roles & Permissions', 'slug' => 'roles.manage', 'group' => 'Administration', 'description' => 'Create roles and customize granted permissions'],
        ];

        $permissionModels = [];
        foreach ($permissions as $p) {
            $permissionModels[$p['slug']] = Permission::updateOrCreate(['slug' => $p['slug']], $p);
        }

        // 2. Create Standard Roles
        $superAdminRole = Role::updateOrCreate(
            ['slug' => 'super_admin'],
            [
                'name' => 'Super Administrator',
                'description' => 'Full unrestricted access to all company leads, revenue, reports, and settings.',
            ]
        );
        $superAdminRole->permissions()->sync(collect($permissionModels)->pluck('id'));

        $salesManagerRole = Role::updateOrCreate(
            ['slug' => 'sales_manager'],
            [
                'name' => 'Sales Manager / Team Lead',
                'description' => 'Oversees sales reps, distributes leads, audits calls, and views company performance.',
            ]
        );
        $salesManagerRole->permissions()->sync([
            $permissionModels['leads.view_all']->id,
            $permissionModels['leads.create']->id,
            $permissionModels['leads.edit']->id,
            $permissionModels['leads.assign']->id,
            $permissionModels['leads.import']->id,
            $permissionModels['calls.log']->id,
            $permissionModels['calls.view_all']->id,
            $permissionModels['meetings.manage']->id,
            $permissionModels['meetings.view_all']->id,
            $permissionModels['deals.manage']->id,
            $permissionModels['deals.view_all']->id,
            $permissionModels['reports.view_all']->id,
            $permissionModels['team.manage']->id,
        ]);

        $salesAgentRole = Role::updateOrCreate(
            ['slug' => 'sales_agent'],
            [
                'name' => 'Sales Representative (Cold Caller)',
                'description' => 'Focuses on assigned cold calling leads, booking meetings and daily outreach.',
            ]
        );
        $salesAgentRole->permissions()->sync([
            $permissionModels['leads.view_assigned']->id,
            $permissionModels['leads.create']->id,
            $permissionModels['leads.edit']->id,
            $permissionModels['leads.import']->id,
            $permissionModels['calls.log']->id,
            $permissionModels['meetings.manage']->id,
            $permissionModels['deals.manage']->id,
            $permissionModels['reports.view_own']->id,
        ]);

        $closerRole = Role::updateOrCreate(
            ['slug' => 'closer'],
            [
                'name' => 'Account Executive / Closer',
                'description' => 'Handles warm discovery meetings, pitches solutions, and signs client contracts.',
            ]
        );
        $closerRole->permissions()->sync([
            $permissionModels['leads.view_all']->id,
            $permissionModels['meetings.manage']->id,
            $permissionModels['meetings.view_all']->id,
            $permissionModels['deals.manage']->id,
            $permissionModels['deals.view_all']->id,
            $permissionModels['reports.view_own']->id,
        ]);

        // 3. Create Super Admin User
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@prowave.com'],
            [
                'name' => 'ProWave Admin',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'role_id' => $superAdminRole->id,
                'phone' => '+92 300 1234567',
                'status' => 'active',
            ]
        );

        // 4. Create Sales Agents
        $agentAli = User::updateOrCreate(
            ['email' => 'ali@prowave.com'],
            [
                'name' => 'Ali Khan (Sales Executive)',
                'password' => Hash::make('password123'),
                'role' => 'sales_agent',
                'role_id' => $salesAgentRole->id,
                'phone' => '+92 321 9876543',
                'status' => 'active',
            ]
        );

        $agentUsman = User::updateOrCreate(
            ['email' => 'usman@prowave.com'],
            [
                'name' => 'Usman Tariq (Sales Executive)',
                'password' => Hash::make('password123'),
                'role' => 'sales_agent',
                'role_id' => $salesAgentRole->id,
                'phone' => '+92 333 4567890',
                'status' => 'active',
            ]
        );

        // 5. Create Sample Leads for Ali Khan
        $leadAli1 = Lead::create([
            'assigned_to' => $agentAli->id,
            'created_by' => $superAdmin->id,
            'lead_type' => 'own',
            'client_name' => 'David Miller (Apex Logistics)',
            'company_name' => 'Apex Logistics LLC',
            'email' => 'david@apexlogistics.com',
            'phone' => '+1 (555) 234-8971',
            'country' => 'USA',
            'service_needed' => 'Custom ERP & Tracking Software',
            'source' => 'Cold Calling',
            'status' => 'meeting_scheduled',
            'budget' => 4500.00,
            'currency' => 'USD',
            'notes' => 'Looking to automate driver dispatch & invoice tracking. Very positive response on cold call.',
            'last_contacted_at' => Carbon::now()->subHours(2),
        ]);

        $leadAli2 = Lead::create([
            'assigned_to' => $agentAli->id,
            'created_by' => $superAdmin->id,
            'lead_type' => 'outsource',
            'client_name' => 'Sarah Jenkins (Bloom Boutique)',
            'company_name' => 'Bloom Boutique Store',
            'email' => 'sarah@bloomboutique.co.uk',
            'phone' => '+44 20 7946 0912',
            'country' => 'UK',
            'service_needed' => 'Shopify / E-Commerce Redesign',
            'source' => 'LinkedIn Outreach',
            'status' => 'interested',
            'budget' => 2200.00,
            'currency' => 'USD',
            'notes' => 'Asked for portfolio of fashion websites. Wants call back tomorrow 3 PM UK time.',
            'last_contacted_at' => Carbon::now()->subHours(5),
        ]);

        $leadAli3 = Lead::create([
            'assigned_to' => $agentAli->id,
            'created_by' => $superAdmin->id,
            'lead_type' => 'own',
            'client_name' => 'Michael Chang (FinTech Nova)',
            'company_name' => 'FinTech Nova',
            'email' => 'mchang@fintechnova.io',
            'phone' => '+1 (415) 890-1234',
            'country' => 'USA',
            'service_needed' => 'Mobile App (Flutter + Laravel API)',
            'source' => 'Cold Calling',
            'status' => 'deal_won',
            'budget' => 6800.00,
            'currency' => 'USD',
            'notes' => 'Deal closed! Contract signed for MVP mobile app. Super excited to start development.',
            'last_contacted_at' => Carbon::now()->subDays(1),
        ]);

        $leadAli4 = Lead::create([
            'assigned_to' => $agentAli->id,
            'created_by' => $superAdmin->id,
            'lead_type' => 'own',
            'client_name' => 'Robert Fox (Fox Dental Care)',
            'company_name' => 'Fox Dental Care',
            'email' => 'info@foxdentalcare.com',
            'phone' => '+1 (312) 555-7890',
            'country' => 'USA',
            'service_needed' => 'Clinic Management & SEO',
            'source' => 'Cold Calling',
            'status' => 'callback',
            'budget' => 1500.00,
            'currency' => 'USD',
            'notes' => 'Doctor was busy with patient. Said please call back today at 5:00 PM EST.',
            'last_contacted_at' => Carbon::now()->subHours(3),
        ]);

        $leadAli5 = Lead::create([
            'assigned_to' => $agentAli->id,
            'created_by' => $superAdmin->id,
            'lead_type' => 'outsource',
            'client_name' => 'Elena Rostova (Skyline Travels)',
            'company_name' => 'Skyline Travels',
            'email' => 'elena@skylinetravels.ae',
            'phone' => '+971 50 123 4567',
            'country' => 'UAE',
            'service_needed' => 'Booking Portal Development',
            'source' => 'Cold Calling',
            'status' => 'new',
            'budget' => 3000.00,
            'currency' => 'USD',
            'notes' => 'New lead assigned for today cold outreach.',
            'last_contacted_at' => null,
        ]);

        // 6. Create Leads for Usman Tariq
        $leadUsman1 = Lead::create([
            'assigned_to' => $agentUsman->id,
            'created_by' => $superAdmin->id,
            'lead_type' => 'own',
            'client_name' => 'James Wilson (Wilson Real Estate)',
            'company_name' => 'Wilson Real Estate',
            'email' => 'james@wilsonproperties.com.au',
            'phone' => '+61 2 9876 5432',
            'country' => 'Australia',
            'service_needed' => 'Property Listing Web Portal',
            'source' => 'Cold Calling',
            'status' => 'meeting_scheduled',
            'budget' => 5200.00,
            'currency' => 'USD',
            'notes' => 'Interested in MLS property integration. Demo meeting setup for tomorrow.',
            'last_contacted_at' => Carbon::now()->subHours(4),
        ]);

        $leadUsman2 = Lead::create([
            'assigned_to' => $agentUsman->id,
            'created_by' => $superAdmin->id,
            'lead_type' => 'outsource',
            'client_name' => 'Amanda Green (EcoClean Services)',
            'company_name' => 'EcoClean Services',
            'email' => 'amanda@ecocleanservices.com',
            'phone' => '+1 (718) 444-9988',
            'country' => 'USA',
            'service_needed' => 'Local SEO & Lead Generation Website',
            'source' => 'Cold Calling',
            'status' => 'deal_won',
            'budget' => 3400.00,
            'currency' => 'USD',
            'notes' => 'Approved 3-month SEO and new landing page setup. Payment upfront received.',
            'last_contacted_at' => Carbon::now()->subDays(2),
        ]);

        $leadUsman3 = Lead::create([
            'assigned_to' => $agentUsman->id,
            'created_by' => $superAdmin->id,
            'lead_type' => 'own',
            'client_name' => 'Klaus Weber (Bavaria Parts)',
            'company_name' => 'Bavaria Parts GmbH',
            'email' => 'klaus@bavariaparts.de',
            'phone' => '+49 89 1234567',
            'country' => 'Germany',
            'service_needed' => 'B2B Wholesale Portal',
            'source' => 'Cold Calling',
            'status' => 'contacted',
            'budget' => 4000.00,
            'currency' => 'USD',
            'notes' => 'Initial pitch made. Sent company brochure over email.',
            'last_contacted_at' => Carbon::now()->subHours(6),
        ]);

        // 7. Create Call Logs for Ali
        CallLog::create([
            'lead_id' => $leadAli1->id,
            'user_id' => $agentAli->id,
            'call_status' => 'meeting_scheduled',
            'remarks' => 'Connected with David. Pitched custom logistics dashboard. Agreed for Zoom demo meeting today at 4:30 PM!',
            'callback_at' => Carbon::today()->setTime(16, 30),
            'call_duration_seconds' => 380,
            'created_at' => Carbon::now()->subHours(2),
        ]);

        CallLog::create([
            'lead_id' => $leadAli2->id,
            'user_id' => $agentAli->id,
            'call_status' => 'interested',
            'remarks' => 'Sarah loved our previous Shopify case studies. Requested a callback tomorrow with pricing tier breakdown.',
            'callback_at' => Carbon::tomorrow()->setTime(15, 0),
            'call_duration_seconds' => 240,
            'created_at' => Carbon::now()->subHours(5),
        ]);

        // 8. Create Meetings
        Meeting::create([
            'lead_id' => $leadAli1->id,
            'user_id' => $agentAli->id,
            'title' => 'Product Demo - Apex Logistics ERP',
            'scheduled_at' => Carbon::today()->setTime(16, 30),
            'meeting_link' => 'https://meet.google.com/prowave-apex-demo',
            'status' => 'scheduled',
            'agenda' => 'Demonstrate driver live tracking dashboard, dispatch workflow, and discuss project scope.',
            'outcome_notes' => 'Awaiting call with client.',
        ]);

        Meeting::create([
            'lead_id' => $leadUsman1->id,
            'user_id' => $agentUsman->id,
            'title' => 'Real Estate Portal Walkthrough - Wilson Properties',
            'scheduled_at' => Carbon::tomorrow()->setTime(11, 0),
            'meeting_link' => 'https://meet.google.com/prowave-wilson-prop',
            'status' => 'scheduled',
            'agenda' => 'Walkthrough of real estate listing template, CRM lead capture, and price quote.',
            'outcome_notes' => null,
        ]);

        // 9. Create Won Deals
        Deal::create([
            'lead_id' => $leadAli3->id,
            'user_id' => $agentAli->id,
            'client_name' => 'Michael Chang (FinTech Nova)',
            'project_title' => 'FinTech Mobile App & Payment Gateway API',
            'project_scope' => 'Cross-platform mobile application for iOS and Android with secure wallet, biometric auth, and real-time transaction notifications.',
            'deal_amount' => 6800.00,
            'currency' => 'USD',
            'closed_date' => Carbon::today()->subDays(1),
            'status' => 'won',
            'notes' => '50% upfront milestone paid ($3,400). Deliverable timeline is 6 weeks. Development kick-off scheduled.',
        ]);

        Deal::create([
            'lead_id' => $leadUsman2->id,
            'user_id' => $agentUsman->id,
            'client_name' => 'Amanda Green (EcoClean Services)',
            'project_title' => 'EcoClean SEO & Corporate Website',
            'project_scope' => 'Complete rebranding, high-converting landing pages, speed optimization, and 3-month local SEO campaign in New York.',
            'deal_amount' => 3400.00,
            'currency' => 'USD',
            'closed_date' => Carbon::today()->subDays(2),
            'status' => 'won',
            'notes' => 'Full payment received. Great customer satisfaction potential for annual recurring maintenance.',
        ]);

        // 10. Create Daily Performance Reports
        DailyReport::create([
            'user_id' => $agentAli->id,
            'report_date' => Carbon::today()->toDateString(),
            'total_calls' => 18,
            'interested_count' => 4,
            'callbacks_count' => 5,
            'meetings_booked' => 1,
            'deals_won' => 0,
            'notes' => 'High engagement today with US logistics leads.',
        ]);

        DailyReport::create([
            'user_id' => $agentUsman->id,
            'report_date' => Carbon::today()->toDateString(),
            'total_calls' => 15,
            'interested_count' => 3,
            'callbacks_count' => 4,
            'meetings_booked' => 1,
            'deals_won' => 0,
            'notes' => 'Australian timezone outreach completed with 1 solid meeting booked.',
        ]);
    }
}
