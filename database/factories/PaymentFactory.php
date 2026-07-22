<?php

namespace Database\Factories;

use App\Models\CustomerService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_service_id' => CustomerService::factory(),
            'request_id' => Str::uuid(),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'status' => $this->faker->randomElement(['PENDING', 'RECEBIDO', 'CONFIRMADO', 'PAGO']),
            'pix_copy_paste' => $this->faker->optional()->text(100),
            'barcode' => $this->faker->optional()->numerify('#####################'),
            'digitable_line' => $this->faker->optional()->text(50),
            'your_number' => $this->faker->optional()->numerify('###########'),
            'payment_method' => $this->faker->optional()->randomElement(['PIX', 'BOLETO', 'CARTAO']),
            'our_number' => $this->faker->optional()->numerify('###########'),
            'txid' => $this->faker->optional()->text(40),
            'paid_at' => $this->faker->optional()->dateTime(),
        ];
    }
}
