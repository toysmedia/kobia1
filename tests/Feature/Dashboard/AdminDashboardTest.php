<?php

namespace Tests\Feature\Dashboard;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for the Admin (Tenant) Dashboard.
 *
 * Covers:
 *  - ISP dashboard loads for authenticated admin
 *  - Unauthenticated access redirects to admin login
 *  - Key sub-pages load correctly (subscribers, routers, packages, sessions, payments)
 */
class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
    }

    // -------------------------------------------------------------------------
    // Dashboard home
    // -------------------------------------------------------------------------

    public function test_isp_dashboard_loads_for_authenticated_admin(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/isp/dashboard');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_admin_is_redirected_from_dashboard(): void
    {
        $response = $this->get('/admin/isp/dashboard');

        $response->assertRedirect('/admin/login');
    }

    // -------------------------------------------------------------------------
    // Subscribers management
    // -------------------------------------------------------------------------

    public function test_subscribers_index_loads(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/isp/subscribers');

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // Router management
    // -------------------------------------------------------------------------

    public function test_routers_index_loads(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/isp/routers');

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // Package management
    // -------------------------------------------------------------------------

    public function test_packages_index_loads(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/isp/packages');

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // Session monitoring
    // -------------------------------------------------------------------------

    public function test_sessions_index_loads(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/isp/sessions');

        $response->assertStatus(200);
    }
}
