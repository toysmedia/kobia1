<?php

namespace Tests\Feature\Auth;

use App\Models\System\SuperAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Tests for Super Admin authentication flows.
 *
 * Covers:
 *  - Login form is accessible at /super-admin/login
 *  - Valid credentials redirect to /super-admin/dashboard
 *  - Invalid credentials return validation error
 *  - Authenticated super admin can access dashboard
 *  - Unauthenticated access to dashboard redirects to login
 *  - Logout clears session and redirects to login
 */
class SuperAdminAuthTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Login form
    // -------------------------------------------------------------------------

    public function test_login_form_is_accessible(): void
    {
        $response = $this->get('/super-admin/login');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_dashboard_access_redirects_to_login(): void
    {
        $response = $this->get('/super-admin/dashboard');

        $response->assertRedirect('/super-admin/login');
    }

    // -------------------------------------------------------------------------
    // Login with valid credentials
    // -------------------------------------------------------------------------

    public function test_super_admin_can_login_with_valid_credentials(): void
    {
        $admin = SuperAdmin::factory()->create([
            'email'     => 'sa@test.com',
            'password'  => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->post('/super-admin/login', [
            'email'    => 'sa@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/super-admin/dashboard');
        $this->assertAuthenticatedAs($admin, 'super_admin');
    }

    // -------------------------------------------------------------------------
    // Login with invalid credentials
    // -------------------------------------------------------------------------

    public function test_login_fails_with_invalid_credentials(): void
    {
        SuperAdmin::factory()->create([
            'email'    => 'sa@test.com',
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->post('/super-admin/login', [
            'email'    => 'sa@test.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('super_admin');
    }

    // -------------------------------------------------------------------------
    // Authenticated dashboard access
    // -------------------------------------------------------------------------

    public function test_authenticated_super_admin_can_access_dashboard(): void
    {
        $admin = SuperAdmin::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin, 'super_admin')
            ->get('/super-admin/dashboard');

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // Logout
    // -------------------------------------------------------------------------

    public function test_super_admin_can_logout(): void
    {
        $admin = SuperAdmin::factory()->create();

        $response = $this->actingAs($admin, 'super_admin')
            ->post('/super-admin/logout');

        $response->assertRedirect('/super-admin/login');
        $this->assertGuest('super_admin');
    }
}
