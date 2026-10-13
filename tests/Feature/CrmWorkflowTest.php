<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Lead;
use App\Models\CallLog;
use App\Models\Meeting;
use App\Models\Deal;
use Carbon\Carbon;

class CrmWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('ProWave CRM');
    }

    public function test_super_admin_can_access_admin_dashboard(): void
    {
        $admin = User::where('role', 'super_admin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Command Center');
        $response->assertSee('Sales Team Performance Leaderboard');
    }

    public function test_sales_agent_can_access_agent_dashboard_with_isolation(): void
    {
        $agentAli = User::where('email', 'ali@prowave.com')->first();
        $this->assertNotNull($agentAli);

        $response = $this->actingAs($agentAli)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Sales Rep Workspace');
        $response->assertSee('Priority Calling Queue');
    }

    public function test_agent_data_isolation_on_leads_pool(): void
    {
        $agentAli = User::where('email', 'ali@prowave.com')->first();
        $agentUsman = User::where('email', 'usman@prowave.com')->first();

        // Lead assigned to Usman
        $usmanLead = Lead::where('assigned_to', $agentUsman->id)->first();
        $this->assertNotNull($usmanLead);

        // Ali should NOT see Usman's lead in his leads listing
        $response = $this->actingAs($agentAli)->get('/leads');
        $response->assertStatus(200);
        $response->assertDontSee($usmanLead->client_name);

        // Ali should get 403 Forbidden if attempting to access Usman's lead detail
        $forbiddenResponse = $this->actingAs($agentAli)->get("/leads/{$usmanLead->id}");
        $forbiddenResponse->assertStatus(403);
    }

    public function test_logging_cold_call_updates_lead_and_creates_meeting(): void
    {
        $agentAli = User::where('email', 'ali@prowave.com')->first();
        $aliLead = Lead::where('assigned_to', $agentAli->id)->first();

        $meetingTime = Carbon::tomorrow()->setTime(14, 0)->format('Y-m-d\TH:i');

        $response = $this->actingAs($agentAli)->post("/leads/{$aliLead->id}/call-logs", [
            'call_status' => 'meeting_scheduled',
            'remarks' => 'Client agreed for a demo meeting tomorrow at 2 PM!',
            'meeting_title' => 'Product Demo with ' . $aliLead->client_name,
            'meeting_time' => $meetingTime,
            'meeting_link' => 'https://meet.google.com/test-prowave-call',
            'call_duration_seconds' => 180,
        ]);

        $response->assertRedirect();
        
        // Assert lead status updated
        $this->assertDatabaseHas('leads', [
            'id' => $aliLead->id,
            'status' => 'meeting_scheduled',
        ]);

        // Assert meeting was created
        $this->assertDatabaseHas('meetings', [
            'lead_id' => $aliLead->id,
            'user_id' => $agentAli->id,
            'status' => 'scheduled',
        ]);
    }

    public function test_logging_deal_won_stores_budget_and_shows_on_deals(): void
    {
        $agentAli = User::where('email', 'ali@prowave.com')->first();
        $aliLead = Lead::where('assigned_to', $agentAli->id)->first();

        $response = $this->actingAs($agentAli)->post("/leads/{$aliLead->id}/call-logs", [
            'call_status' => 'deal_won',
            'remarks' => 'Contract signed! Initial 50% invoice dispatched.',
            'deal_amount' => 7500.00,
            'project_title' => 'Custom Logistics Dashboard System',
            'project_scope' => 'Complete driver tracking, automated billing, and live dispatch map.',
            'call_duration_seconds' => 300,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('deals', [
            'lead_id' => $aliLead->id,
            'deal_amount' => 7500.00,
            'status' => 'won',
        ]);

        // Super Admin sees the won deal in deals showcase
        $admin = User::where('role', 'super_admin')->first();
        $dealViewResponse = $this->actingAs($admin)->get('/deals');
        $dealViewResponse->assertStatus(200);
        $dealViewResponse->assertSee('7,500.00');
    }

    public function test_download_import_template(): void
    {
        $admin = User::where('role', 'super_admin')->first();
        $response = $this->actingAs($admin)->get('/leads-import/template');
        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename="prowave_leads_import_template.csv"');
    }

    public function test_bulk_lead_import_from_csv(): void
    {
        $admin = User::where('role', 'super_admin')->first();

        $csvContent = "Client Name,Company Name,Phone,Email,Country,Estimated Budget\n" .
                      "Bruce Wayne,Wayne Enterprises,+1 (555) 999-1122,bruce@wayne.com,USA,12000.00\n" .
                      "Diana Prince,Themyscira Antiques,+1 (555) 888-3344,diana@museum.org,USA,6500.00\n";

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('leads.csv', $csvContent);

        $response = $this->actingAs($admin)->post('/leads-import', [
            'file' => $file,
            'assignment_mode' => 'round_robin',
        ]);

        $response->assertRedirect('/leads');

        $this->assertDatabaseHas('leads', [
            'client_name' => 'Bruce Wayne',
            'phone' => '+1 (555) 999-1122',
        ]);

        $this->assertDatabaseHas('leads', [
            'client_name' => 'Diana Prince',
            'phone' => '+1 (555) 888-3344',
        ]);
    }

    public function test_super_admin_can_access_roles_management(): void
    {
        $admin = User::where('role', 'super_admin')->first();
        $response = $this->actingAs($admin)->get('/roles');
        $response->assertStatus(200);
        $response->assertSee('User Roles & Permissions');
        $response->assertSee('Sales Manager');
    }

    public function test_sales_agent_cannot_access_roles_management(): void
    {
        $agentAli = User::where('email', 'ali@prowave.com')->first();
        $response = $this->actingAs($agentAli)->get('/roles');
        $response->assertStatus(403);
    }

    public function test_super_admin_can_create_custom_role_with_permissions(): void
    {
        $admin = User::where('role', 'super_admin')->first();

        $perm = \App\Models\Permission::where('slug', 'calls.log')->first();

        $response = $this->actingAs($admin)->post('/roles', [
            'name' => 'Night Shift Cold Caller',
            'description' => 'Dedicated representative for US timezone calling',
            'permissions' => [$perm->id],
        ]);

        $response->assertRedirect('/roles');

        $this->assertDatabaseHas('roles', [
            'name' => 'Night Shift Cold Caller',
            'slug' => 'night_shift_cold_caller',
        ]);
    }
}
