<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfigurationDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_configuration_page_loads_for_authenticated_admin(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/isp/configuration');

        $response->assertStatus(200);
        $response->assertSee('Server Configuration');
    }

    public function test_services_status_endpoint_returns_json_structure(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->getJson('/admin/isp/configuration/services-status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'services',
                    'timestamp',
                ],
            ]);
    }

    public function test_restart_service_rejects_invalid_service_value(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->postJson('/admin/isp/configuration/restart-service', [
                'service' => 'invalid-service',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['service']);
    }
}
