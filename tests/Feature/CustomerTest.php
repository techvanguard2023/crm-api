<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_returns_customers(): void
    {
        Customer::factory(5)->create();

        $response = $this->actingAs($this->user)->getJson('/api/v1/customers');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                ]
            ]
        ]);
    }

    public function test_store_creates_customer(): void
    {
        $payload = [
            'name' => 'John Doe',
            'company_name' => 'Acme Corp',
            'email' => 'john@example.com',
            'phone' => '11999999999',
            'type' => 'PF',
            'document' => '12345678900',
            'address' => '123 Main Street',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01234-567',
            'country' => 'Brazil',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/customers', $payload);

        $response->assertStatus(201);
        $response->assertJsonFragment(['name' => 'John Doe']);

        $this->assertDatabaseHas('customers', [
            'email' => 'john@example.com',
            'document' => '12345678900',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $payload = [
            'name' => 'John Doe',
            // Missing required fields
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/customers', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'phone']);
    }

    public function test_store_validates_unique_email(): void
    {
        $existingCustomer = Customer::factory()->create(['email' => 'duplicate@example.com']);

        $payload = [
            'name' => 'Jane Doe',
            'email' => 'duplicate@example.com',
            'phone' => '11999999999',
            'type' => 'PF',
            'document' => '98765432100',
            'address' => '456 Oak Avenue',
            'city' => 'Rio de Janeiro',
            'state' => 'RJ',
            'zip_code' => '20000-000',
            'country' => 'Brazil',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/customers', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_update_customer(): void
    {
        $customer = Customer::factory()->create();

        $payload = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/customers/{$customer->id}", $payload);

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Updated Name']);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_destroy_soft_deletes_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/customers/{$customer->id}");

        $response->assertStatus(204);

        // Verify soft delete: customer exists in database but is marked as deleted
        $this->assertSoftDeleted('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_add_service_to_customer(): void
    {
        $customer = Customer::factory()->create();
        $service = Service::factory()->create();

        $payload = [
            'service_id' => $service->id,
            'price' => 150.00,
            'recurrence' => 'monthly',
            'start_date' => now()->format('Y-m-d'),
            'next_due_date' => now()->addMonth()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/customers/{$customer->id}/services", $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('customer_service', [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'price' => 150.00,
            'recurrence' => 'monthly',
        ]);
    }
}
