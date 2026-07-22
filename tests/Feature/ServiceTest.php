<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_returns_services_list(): void
    {
        Service::factory(3)->create();

        $response = $this->actingAs($this->user)->getJson('/api/v1/services');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                ]
            ]
        ]);
    }

    public function test_store_creates_service(): void
    {
        $payload = [
            'name' => 'Premium Support',
            'description' => 'Premium technical support package',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/services', $payload);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'name' => 'Premium Support',
            'description' => 'Premium technical support package',
        ]);

        $this->assertDatabaseHas('services', [
            'name' => 'Premium Support',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $payload = [
            'name' => 'Test Service',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/services', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['description']);
    }

    public function test_show_returns_service(): void
    {
        $service = Service::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/services/' . $service->id);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $service->id,
            'name' => $service->name,
        ]);
    }

    public function test_update_service(): void
    {
        $service = Service::factory()->create();

        $payload = [
            'name' => 'Updated Service Name',
            'description' => 'Updated description',
        ];

        $response = $this->actingAs($this->user)
            ->putJson('/api/v1/services/' . $service->id, $payload);

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Updated Service Name']);

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Updated Service Name',
        ]);
    }

    public function test_destroy_deletes_service(): void
    {
        $service = Service::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/v1/services/' . $service->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('services', [
            'id' => $service->id,
        ]);
    }
}
