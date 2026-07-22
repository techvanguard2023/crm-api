<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_dashboard_returns_correct_structure(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/dashboard/metrics');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'counts' => [
                'total_customers',
                'total_domains',
                'total_active_services',
            ],
            'financial' => [
                'gross_month',
                'expenses_month',
                'net_month',
                'gross_year',
                'expenses_year',
                'net_year',
            ],
            'annual_projection' => [
                '*' => [
                    'month_number',
                    'month_name',
                    'revenue',
                    'expenses',
                    'net',
                ]
            ]
        ]);
    }

    public function test_dashboard_monthly_revenue_is_calculated_correctly(): void
    {
        // Create a customer with a monthly service
        $customer = Customer::factory()->create();
        $service = Service::factory()->create();

        $today = now();
        $customer->services()->attach($service->id, [
            'price' => 100.00,
            'recurrence' => 'monthly',
            'start_date' => $today->copy()->subMonth(),
            'next_due_date' => $today,  // Set due date to today (current month)
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/dashboard/metrics');

        $response->assertStatus(200);
        // The monthly revenue is under financial.gross_month
        $response->assertJsonPath('financial.gross_month', '100.00');
    }

    public function test_dashboard_annual_projection_has_12_months(): void
    {
        // Create test data to ensure we have some services
        $customer = Customer::factory()->create();
        $service = Service::factory()->create();

        $customer->services()->attach($service->id, [
            'price' => 100.00,
            'recurrence' => 'monthly',
            'start_date' => now()->subMonth(),
            'next_due_date' => now()->addMonth(),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/dashboard/metrics');

        $response->assertStatus(200);
        // Verify projection array exists and has 12 months
        $projectionCount = count($response->json('annual_projection', []));
        $this->assertEquals(12, $projectionCount, 'Projection should have exactly 12 months');
    }
}
