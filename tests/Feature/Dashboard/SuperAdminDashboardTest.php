<?php

namespace Tests\Feature\Dashboard;

use App\Models\System\SuperAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for the Super Admin Dashboard.
 *
 * Covers:
 *  - Dashboard returns 200 for authenticated super admin
 *  - Unauthenticated access is redirected to login
 *  - Key sub-pages (tenants, subscriptions, pricing, CMS) load correctly
 */
class SuperAdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private SuperAdmin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = SuperAdmin::factory()->create(['is_active' => true]);
    }

    // -------------------------------------------------------------------------
    // Dashboard home
    // -------------------------------------------------------------------------

    public function test_dashboard_loads_for_authenticated_super_admin(): void
    {
        $response = $this->actingAs($this->admin, 'super_admin')
            ->get('/super-admin/dashboard');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_super_admin_is_redirected_from_dashboard(): void
    {
        $response = $this->get('/super-admin/dashboard');

        $response->assertRedirect('/super-admin/login');
    }

    // -------------------------------------------------------------------------
    // Tenant management
    // -------------------------------------------------------------------------

    public function test_tenants_index_loads(): void
    {
        $response = $this->actingAs($this->admin, 'super_admin')
            ->get('/super-admin/tenants');

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // Subscription management
    // -------------------------------------------------------------------------

    public function test_subscriptions_index_loads(): void
    {
        $response = $this->actingAs($this->admin, 'super_admin')
            ->get('/super-admin/subscriptions');

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // Pricing plans
    // -------------------------------------------------------------------------

    public function test_pricing_plans_index_loads(): void
    {
        $response = $this->actingAs($this->admin, 'super_admin')
            ->get('/super-admin/pricing-plans');

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // CMS
    // -------------------------------------------------------------------------

    public function test_cms_page_loads(): void
    {
        $response = $this->actingAs($this->admin, 'super_admin')
            ->get('/super-admin/cms');

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // Audit logs
    // -------------------------------------------------------------------------

    public function test_audit_logs_page_loads(): void
    {
        $response = $this->actingAs($this->admin, 'super_admin')
            ->get('/super-admin/audit-logs');

        $response->assertStatus(200);
    }
}
