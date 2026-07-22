<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_metrics_returns_expense_summary(): void
    {
        Expense::factory()->create(['amount' => 100.00, 'status' => 'paid']);
        Expense::factory()->create(['amount' => 50.00, 'status' => 'pending']);

        $response = $this->actingAs($this->user)->getJson('/api/v1/expenses/metrics');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_geral',
            'total_pago',
            'total_pendente',
            'total_categorias',
        ]);
    }

    public function test_index_returns_expenses_list(): void
    {
        Expense::factory(3)->create();

        $response = $this->actingAs($this->user)->getJson('/api/v1/expenses');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'amount',
                    'date',
                    'recurrence',
                    'category',
                    'status',
                ]
            ]
        ]);
    }

    public function test_store_creates_expense(): void
    {
        $payload = [
            'name' => 'Server Hosting',
            'description' => 'Monthly server hosting fee',
            'amount' => 299.99,
            'date' => now()->format('Y-m-d'),
            'recurrence' => 'monthly',
            'category' => 'infrastructure',
            'status' => 'pending',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/expenses', $payload);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'name' => 'Server Hosting',
            'amount' => '299.99',
        ]);

        $this->assertDatabaseHas('expenses', [
            'name' => 'Server Hosting',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $payload = [
            'name' => 'Test Expense',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/expenses', $payload);

        $response->assertStatus(422);
    }

    public function test_show_returns_expense(): void
    {
        $expense = Expense::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/expenses/' . $expense->id);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $expense->id,
            'name' => $expense->name,
        ]);
    }

    public function test_update_expense(): void
    {
        $expense = Expense::factory()->create();

        $payload = [
            'amount' => 499.99,
            'status' => 'paid',
        ];

        $response = $this->actingAs($this->user)
            ->putJson('/api/v1/expenses/' . $expense->id, $payload);

        $response->assertStatus(200);
        $response->assertJsonFragment(['amount' => '499.99']);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'amount' => 499.99,
        ]);
    }

    public function test_destroy_soft_deletes_expense(): void
    {
        $expense = Expense::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/v1/expenses/' . $expense->id);

        $response->assertStatus(204);

        $this->assertSoftDeleted('expenses', [
            'id' => $expense->id,
        ]);
    }
}
