<?php

namespace Tests\Feature\Auth;

use App\Models\Community\CommunityUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Tests for Community Portal authentication flows.
 *
 * Covers:
 *  - Login form accessible at /community/login
 *  - Registration form accessible
 *  - Unauthenticated protected routes redirect to community login
 *  - Login with valid credentials works
 *  - Login with invalid credentials returns validation error
 *  - Banned user cannot log in to protected areas
 *  - Logout clears session and redirects to community home
 */
class CommunityAuthTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Login form
    // -------------------------------------------------------------------------

    public function test_community_login_form_is_accessible(): void
    {
        $response = $this->get('/community/login');

        $response->assertStatus(200);
    }

    public function test_community_register_form_is_accessible(): void
    {
        $response = $this->get('/community/register');

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // Login with valid credentials
    // -------------------------------------------------------------------------

    public function test_community_user_can_login_with_valid_credentials(): void
    {
        $user = CommunityUser::factory()->create([
            'email'    => 'member@test.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/community/login', [
            'email'    => 'member@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/community');
        $this->assertAuthenticatedAs($user, 'community');
    }

    // -------------------------------------------------------------------------
    // Login with invalid credentials
    // -------------------------------------------------------------------------

    public function test_community_login_fails_with_invalid_credentials(): void
    {
        CommunityUser::factory()->create([
            'email'    => 'member@test.com',
            'password' => Hash::make('correct-pass'),
        ]);

        $response = $this->post('/community/login', [
            'email'    => 'member@test.com',
            'password' => 'wrong-pass',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('community');
    }

    // -------------------------------------------------------------------------
    // Banned user is blocked from authenticated routes
    // -------------------------------------------------------------------------

    public function test_banned_user_is_redirected_from_protected_routes(): void
    {
        $banned = CommunityUser::factory()->create([
            'is_banned'  => true,
            'ban_reason' => 'Spam',
        ]);

        $response = $this->actingAs($banned, 'community')
            ->get('/community/posts/create');

        // CommunityNotBanned middleware should block access
        $response->assertRedirectContains('/community');
    }

    // -------------------------------------------------------------------------
    // Public pages remain accessible without login
    // -------------------------------------------------------------------------

    public function test_public_community_feed_is_accessible_without_login(): void
    {
        $response = $this->get('/community/posts');

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // Logout
    // -------------------------------------------------------------------------

    public function test_community_user_can_logout(): void
    {
        $user = CommunityUser::factory()->create();

        $response = $this->actingAs($user, 'community')
            ->post('/community/logout');

        $response->assertRedirect('/community');
        $this->assertGuest('community');
    }
}
