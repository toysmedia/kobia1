<?php

namespace Tests\Feature\Auth;

use App\Models\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for Worker/Seller (Admin Tenant Staff) authentication flows.
 *
 * Covers:
 *  - Login form accessible at /seller/login
 *  - Unauthenticated access to seller dashboard redirects to login
 *  - Login with valid credentials redirects to seller dashboard
 *  - Login with invalid credentials returns validation error
 *  - Logout clears session and redirects to seller login
 */
class SellerAuthTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Login form
    // -------------------------------------------------------------------------

    public function test_seller_login_form_is_accessible(): void
    {
        $response = $this->get('/seller/login');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_seller_dashboard_redirects_to_login(): void
    {
        $response = $this->get('/seller/dashboard');

        $response->assertRedirect('/seller/login');
    }

    // -------------------------------------------------------------------------
    // Login with valid credentials
    // -------------------------------------------------------------------------

    public function test_seller_can_login_with_valid_credentials(): void
    {
        $seller = Seller::factory()->create([
            'email'    => 'seller@test.com',
            'password' => 'secret123',
        ]);

        $response = $this->post('/seller/login', [
            'login'    => 'seller@test.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/seller/dashboard');
        $this->assertAuthenticatedAs($seller, 'seller');
    }

    // -------------------------------------------------------------------------
    // Login with invalid credentials
    // -------------------------------------------------------------------------

    public function test_seller_login_fails_with_invalid_credentials(): void
    {
        Seller::factory()->create([
            'email'    => 'seller@test.com',
            'password' => 'correct-pass',
        ]);

        $response = $this->post('/seller/login', [
            'email'    => 'seller@test.com',
            'password' => 'wrong-pass',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest('seller');
    }

    // -------------------------------------------------------------------------
    // Logout
    // -------------------------------------------------------------------------

    public function test_seller_can_logout(): void
    {
        $seller = Seller::factory()->create();

        $response = $this->actingAs($seller, 'seller')
            ->post('/seller/logout');

        $response->assertRedirect('/seller/login');
        $this->assertGuest('seller');
    }
}
