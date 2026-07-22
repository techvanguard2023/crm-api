<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerService;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class CustomerServiceTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_renew_updates_service_and_creates_renewal(): void
    {
        $customerService = $this->createCustomerService();
        $originalDueDate = $customerService->next_due_date;

        $payload = [
            'amount' => 100.00,
            'date' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/customer-services/{$customerService->id}/renew", $payload);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'message' => 'Service renewed successfully.',
        ]);

        // Verify renewal record was created
        $this->assertDatabaseHas('service_renewals', [
            'customer_service_id' => $customerService->id,
            'amount' => 100.00,
        ]);

        // Verify next_due_date was updated
        $customerService->refresh();
        $this->assertNotEquals($originalDueDate->format('Y-m-d'), $customerService->next_due_date->format('Y-m-d'));
    }

    public function test_ready_to_bill_returns_services_list(): void
    {
        $this->createCustomerService();

        $response = $this->actingAs($this->user)->getJson('/api/v1/customer-services/ready-to-bill');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'customer_name',
                'service_name',
                'domain_name',
                'amount',
                'due_date',
                'status',
                'recurrence',
            ]
        ]);
    }

    public function test_ready_to_bill_metrics_returns_summary(): void
    {
        // Create service overdue
        $customerService = $this->createCustomerService();
        $customerService->update(['next_due_date' => now()->subMonth()]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/customer-services/ready-to-bill-metrics');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'pending_customers',
            'services_due',
            'expected_amount',
        ]);
    }

    public function test_ready_to_bill_filters_by_status(): void
    {
        $overdue = $this->createCustomerService();
        $overdue->update(['next_due_date' => now()->subDays(5)]);

        $dueSoon = $this->createCustomerService();
        $dueSoon->update(['next_due_date' => now()->addDays(5)]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/customer-services/ready-to-bill');

        $response->assertStatus(200);
        $data = $response->json();

        // Verify both services are returned with correct status
        $statuses = array_column($data, 'status');
        $this->assertContains('pending', $statuses);
        $this->assertContains('upcoming', $statuses);
    }

    public function test_renew_validates_required_fields(): void
    {
        $customerService = $this->createCustomerService();

        $payload = [
            // Missing amount
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/customer-services/{$customerService->id}/renew", $payload);

        $response->assertStatus(422);
    }

    private function createCustomerService()
    {
        $customer = Customer::factory()->create();
        $service = Service::factory()->create();

        $customer->services()->attach($service->id, [
            'price' => 100.00,
            'recurrence' => 'monthly',
            'start_date' => now()->subMonth(),
            'next_due_date' => now(),
        ]);

        return $customer->services()->first()->pivot;
    }
}
