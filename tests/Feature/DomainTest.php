<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Domain;
use App\Models\User;
use Tests\TestCase;

class DomainTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_returns_domains_list(): void
    {
        $customer = Customer::factory()->create();
        Domain::factory(3)->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/domains');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'customer_id',
                    'name',
                    'status',
                    'expired_at',
                ]
            ]
        ]);
    }

    public function test_store_creates_domain(): void
    {
        $customer = Customer::factory()->create();

        $payload = [
            'customer_id' => $customer->id,
            'name' => 'example.com',
            'status' => 'active',
            'expired_at' => now()->addYear()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/domains', $payload);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'name' => 'example.com',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('domains', [
            'name' => 'example.com',
            'customer_id' => $customer->id,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $payload = [
            'name' => 'example.com',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/domains', $payload);

        $response->assertStatus(422);
    }

    public function test_store_validates_unique_domain_name(): void
    {
        $customer = Customer::factory()->create();
        Domain::factory()->create(['name' => 'duplicate.com']);

        $payload = [
            'customer_id' => $customer->id,
            'name' => 'duplicate.com',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/domains', $payload);

        $response->assertStatus(422);
    }

    public function test_show_returns_domain(): void
    {
        $domain = Domain::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/domains/' . $domain->id);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $domain->id,
            'name' => $domain->name,
        ]);
    }

    public function test_update_domain(): void
    {
        $domain = Domain::factory()->create();

        $payload = [
            'status' => 'expired',
        ];

        $response = $this->actingAs($this->user)
            ->putJson('/api/v1/domains/' . $domain->id, $payload);

        $response->assertStatus(200);
        $response->assertJsonFragment(['status' => 'expired']);

        $this->assertDatabaseHas('domains', [
            'id' => $domain->id,
            'status' => 'expired',
        ]);
    }

    public function test_destroy_soft_deletes_domain(): void
    {
        $domain = Domain::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/v1/domains/' . $domain->id);

        $response->assertStatus(204);

        $this->assertSoftDeleted('domains', [
            'id' => $domain->id,
        ]);
    }
}
