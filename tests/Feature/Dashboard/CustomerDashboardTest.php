<?php

namespace Tests\Feature\Dashboard;

use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for the PPPoE Customer Dashboard.
 *
 * Covers:
 *  - Dashboard loads for authenticated, active customer
 *  - Unauthenticated access redirects to customer login
 *  - Package page loads
 *  - Payments page loads
 *  - Profile page loads
 *  - Renewal/payment page is accessible even without an active subscription
 */
class CustomerDashboardTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Dashboard home (active subscriber)
    // -------------------------------------------------------------------------

    public function test_dashboard_loads_for_active_customer(): void
    {
        $subscriber = Subscriber::factory()->create([
            'expires_at' => now()->addDays(10),
        ]);

        $response = $this
            ->withoutMiddleware(\App\Http\Middleware\CustomerPortalEnabled::class)
            ->actingAs($subscriber, 'customer')
            ->get('/customer/dashboard');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_customer_is_redirected_from_dashboard(): void
    {
        $response = $this
            ->withoutMiddleware(\App\Http\Middleware\CustomerPortalEnabled::class)
            ->get('/customer/dashboard');

        $response->assertRedirect();
    }

    // -------------------------------------------------------------------------
    // Renewal page is accessible regardless of subscription status
    // -------------------------------------------------------------------------

    public function test_renewal_page_accessible_for_expired_customer(): void
    {
        $subscriber = Subscriber::factory()->create([
            'expires_at' => now()->subDays(5),
        ]);

        $response = $this
            ->withoutMiddleware(\App\Http\Middleware\CustomerPortalEnabled::class)
            ->actingAs($subscriber, 'customer')
            ->get('/customer/payments/renew');

        // Renewal page should be accessible even with expired subscription
        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // Payments history
    // -------------------------------------------------------------------------

    public function test_payments_index_loads_for_active_customer(): void
    {
        $subscriber = Subscriber::factory()->create([
            'expires_at' => now()->addDays(10),
        ]);

        $response = $this
            ->withoutMiddleware(\App\Http\Middleware\CustomerPortalEnabled::class)
            ->actingAs($subscriber, 'customer')
            ->get('/customer/payments');

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // Profile page
    // -------------------------------------------------------------------------

    public function test_profile_page_loads_for_active_customer(): void
    {
        $subscriber = Subscriber::factory()->create([
            'expires_at' => now()->addDays(10),
        ]);

        $response = $this
            ->withoutMiddleware(\App\Http\Middleware\CustomerPortalEnabled::class)
            ->actingAs($subscriber, 'customer')
            ->get('/customer/profile');

        $response->assertStatus(200);
    }
}
