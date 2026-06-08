<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected array $dashboardEndpoints = [
        '/api/dashboard/stats',
        '/api/dashboard/weekly-trend',
        '/api/dashboard/recent-appointments',
        '/api/dashboard/status-distribution',
    ];

    public function test_admin_can_access_dashboard_endpoints(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach ($this->dashboardEndpoints as $endpoint) {
            $response = $this->actingAs($admin)->getJson($endpoint);
            $response->assertStatus(200);
            $response->assertJsonStructure(['success', 'data']);
        }
    }

    public function test_doctor_can_access_dashboard_endpoints(): void
    {
        $doctor = User::factory()->create(['role' => 'doctor']);

        foreach ($this->dashboardEndpoints as $endpoint) {
            $response = $this->actingAs($doctor)->getJson($endpoint);
            $response->assertStatus(200);
            $response->assertJsonStructure(['success', 'data']);
        }
    }

    public function test_receptionist_blocked_from_dashboard_endpoints(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);

        foreach ($this->dashboardEndpoints as $endpoint) {
            $response = $this->actingAs($receptionist)->getJson($endpoint);
            $response->assertStatus(403);
        }
    }

    public function test_nurse_blocked_from_dashboard_endpoints(): void
    {
        $nurse = User::factory()->create(['role' => 'nurse']);

        foreach ($this->dashboardEndpoints as $endpoint) {
            $response = $this->actingAs($nurse)->getJson($endpoint);
            $response->assertStatus(403);
        }
    }

    public function test_unauthenticated_user_blocked_from_dashboard_endpoints(): void
    {
        foreach ($this->dashboardEndpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            $response->assertStatus(401);
        }
    }

    public function test_dashboard_stats_returns_valid_json_structure(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->getJson('/api/dashboard/stats');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'totalPatients',
                'totalDoctors',
                'totalAppointments',
                'todayAppointments',
                'pending',
                'confirmed',
                'completed',
                'cancelled',
            ],
        ]);
    }

    public function test_dashboard_weekly_trend_returns_valid_json(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->getJson('/api/dashboard/weekly-trend');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => ['labels', 'data'],
        ]);
    }

    public function test_dashboard_recent_appointments_returns_valid_json(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->getJson('/api/dashboard/recent-appointments');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'patientName', 'doctorName', 'date', 'time', 'status'],
            ],
        ]);
    }

    public function test_dashboard_status_distribution_returns_valid_json(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->getJson('/api/dashboard/status-distribution');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => ['pending', 'confirmed', 'completed', 'cancelled'],
        ]);
    }
}
