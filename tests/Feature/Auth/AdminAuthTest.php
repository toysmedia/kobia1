<?php

namespace Tests\Feature\Auth;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Tests for Admin (Tenant) authentication flows.
 *
 * Covers:
 *  - Login form accessible at /admin/login
 *  - Valid credentials redirect to /admin/isp/dashboard
 *  - Invalid credentials return validation error
 *  - Unauthenticated access redirects to login
 *  - Logout clears session and redirects to login
 */
class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Login form
    // -------------------------------------------------------------------------

    public function test_admin_login_form_is_accessible(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_admin_dashboard_access_redirects_to_login(): void
    {
        $response = $this->get('/admin/isp/dashboard');

        $response->assertRedirect('/admin/login');
    }

    // -------------------------------------------------------------------------
    // Login with valid credentials
    // -------------------------------------------------------------------------

    public function test_admin_can_login_with_valid_email(): void
    {
        $admin = Admin::factory()->create([
            'email'    => 'admin@test.com',
            'password' => 'secret123',
        ]);

        $response = $this->post('/admin/login', [
            'login'    => 'admin@test.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/admin/isp/dashboard');
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    // -------------------------------------------------------------------------
    // Login with invalid credentials
    // -------------------------------------------------------------------------

    public function test_admin_login_fails_with_invalid_credentials(): void
    {
        Admin::factory()->create([
            'email'    => 'admin@test.com',
            'password' => 'correct-pass',
        ]);

        $response = $this->post('/admin/login', [
            'login'    => 'admin@test.com',
            'password' => 'wrong-pass',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest('admin');
    }

    // -------------------------------------------------------------------------
    // Logout
    // -------------------------------------------------------------------------

    public function test_admin_can_logout(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->post('/admin/logout');

        $response->assertRedirect('/admin/login');
        $this->assertGuest('admin');
    }
}
