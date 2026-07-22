<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_returns_payments_list(): void
    {
        $customerService = $this->createCustomerService();
        Payment::factory(3)->create(['customer_service_id' => $customerService->id]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/payments');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
        $response->assertJsonCount(3, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'request_id',
                    'amount',
                    'status',
                    'pix_copy_paste',
                    'barcode',
                    'digitable_line',
                    'your_number',
                    'payment_method',
                    'paid_at',
                    'created_at',
                    'updated_at',
                    'service_name',
                    'due_date',
                ]
            ]
        ]);
    }

    public function test_store_creates_payment_request(): void
    {
        $customerService = $this->createCustomerService();

        $payload = [
            'amount' => 150.50,
            'request_id' => 'REQ123456',
            'barcode' => '123456789012345678901',
            'pix_copy_paste' => 'pix_code_here',
            'status' => 'PENDING',
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/customer-services/{$customerService->id}/payment-request", $payload);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'request_id' => 'REQ123456',
            'amount' => '150.50',
            'status' => 'PENDING',
        ]);

        $this->assertDatabaseHas('payments', [
            'request_id' => 'REQ123456',
            'customer_service_id' => $customerService->id,
        ]);
    }

    public function test_update_callback_updates_payment_status(): void
    {
        $customerService = $this->createCustomerService();
        $payment = Payment::factory()->create([
            'customer_service_id' => $customerService->id,
            'status' => 'PENDING',
        ]);

        $callbackData = [
            'codigoSolicitacao' => $payment->request_id,
            'situacao' => 'RECEBIDO',
            'valorTotalRecebido' => 200.00,
            'dataHoraSituacao' => now()->format('Y-m-d H:i:s'),
            'seuNumero' => '12345',
            'origemRecebimento' => 'PIX',
            'nossoNumero' => '67890',
            'codigoBarras' => 'barcode123',
            'linhaDigitavel' => 'digitable123',
            'txid' => 'txid123',
            'pixCopiaECola' => 'pix123',
        ];

        $response = $this->actingAs($this->user)
            ->putJson('/api/v1/payments/callback', $callbackData);

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Payment updated successfully']);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'RECEBIDO',
            'amount' => 200.00,
        ]);
    }

    public function test_callback_triggers_renewal_on_payment_confirmed(): void
    {
        $customerService = $this->createCustomerService();
        $originalDueDate = $customerService->next_due_date;

        $payment = Payment::factory()->create([
            'customer_service_id' => $customerService->id,
            'status' => 'PENDING',
            'paid_at' => null,
        ]);

        $callbackData = [
            'codigoSolicitacao' => $payment->request_id,
            'situacao' => 'PAGO',
            'valorTotalRecebido' => $customerService->price,
            'dataHoraSituacao' => now()->format('Y-m-d H:i:s'),
        ];

        $this->actingAs($this->user)->putJson('/api/v1/payments/callback', $callbackData);

        // Verify renewal was created
        $this->assertDatabaseHas('service_renewals', [
            'customer_service_id' => $customerService->id,
        ]);

        // Verify next_due_date was updated
        $customerService->refresh();
        $this->assertNotEquals($originalDueDate->format('Y-m-d'), $customerService->next_due_date->format('Y-m-d'));
    }

    public function test_get_by_request_id_returns_payment(): void
    {
        $customerService = $this->createCustomerService();
        $payment = Payment::factory()->create(['customer_service_id' => $customerService->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/payments/request/{$payment->request_id}");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'request_id' => $payment->request_id,
            'amount' => (string) $payment->amount,
        ]);
    }

    public function test_get_customer_by_request_id(): void
    {
        $customer = Customer::factory()->create();
        $customerService = $customer->services()->attach(
            Service::factory()->create(),
            [
                'price' => 100,
                'recurrence' => 'monthly',
                'start_date' => now(),
                'next_due_date' => now()->addMonth(),
            ]
        );

        $payment = Payment::factory()->create([
            'customer_service_id' => $customer->services()->first()->pivot->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/payments/request/{$payment->request_id}/customer");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
        ]);
    }

    private function createCustomerService()
    {
        $customer = Customer::factory()->create();
        $service = Service::factory()->create();

        $customer->services()->attach($service->id, [
            'price' => 100.00,
            'recurrence' => 'monthly',
            'start_date' => now(),
            'next_due_date' => now()->addMonth(),
        ]);

        return $customer->services()->first()->pivot;
    }
}
