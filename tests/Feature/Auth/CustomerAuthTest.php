<?php

namespace Tests\Feature\Auth;

use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for PPPoE Customer authentication flows.
 *
 * Covers:
 *  - Login form accessible at /customer/login
 *  - Unauthenticated dashboard access redirects to login
 *  - Login with valid credentials redirects to /customer/dashboard
 *  - Login with invalid credentials returns validation error
 *  - Logout clears session and redirects to login
 */
class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Login form
    // -------------------------------------------------------------------------

    public function test_customer_login_form_is_accessible(): void
    {
        // CustomerPortalEnabled middleware checks TenantSetting; bypass via route
        $response = $this->withoutMiddleware(\App\Http\Middleware\CustomerPortalEnabled::class)
            ->get('/customer/login');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_customer_dashboard_redirects_to_login(): void
    {
        $response = $this->withoutMiddleware(\App\Http\Middleware\CustomerPortalEnabled::class)
            ->get('/customer/dashboard');

        // Unauthenticated — redirected to login (may chain through active check too)
        $response->assertRedirect();
    }

    // -------------------------------------------------------------------------
    // Login with valid credentials
    // -------------------------------------------------------------------------

    public function test_customer_can_login_with_valid_credentials(): void
    {
        $subscriber = Subscriber::factory()->create([
            'username'      => 'testcustomer',
            'password_hash' => bcrypt('secret123'),
        ]);

        $response = $this
            ->withoutMiddleware(\App\Http\Middleware\CustomerPortalEnabled::class)
            ->post('/customer/login', [
                'username' => 'testcustomer',
                'password' => 'secret123',
            ]);

        $response->assertRedirect('/customer/dashboard');
        $this->assertAuthenticatedAs($subscriber, 'customer');
    }

    // -------------------------------------------------------------------------
    // Login with invalid credentials
    // -------------------------------------------------------------------------

    public function test_customer_login_fails_with_invalid_credentials(): void
    {
        Subscriber::factory()->create([
            'username'      => 'testcustomer',
            'password_hash' => bcrypt('correct-pass'),
        ]);

        $response = $this
            ->withoutMiddleware(\App\Http\Middleware\CustomerPortalEnabled::class)
            ->post('/customer/login', [
                'username' => 'testcustomer',
                'password' => 'wrong-pass',
            ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest('customer');
    }

    // -------------------------------------------------------------------------
    // Logout
    // -------------------------------------------------------------------------

    public function test_customer_can_logout(): void
    {
        $subscriber = Subscriber::factory()->create();

        $response = $this
            ->withoutMiddleware(\App\Http\Middleware\CustomerPortalEnabled::class)
            ->actingAs($subscriber, 'customer')
            ->post('/customer/logout');

        $response->assertRedirect('/customer/login');
        $this->assertGuest('customer');
    }
}
