<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class DomainFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'name' => $this->faker->unique()->domainName(),
            'status' => $this->faker->randomElement(['active', 'inactive', 'expired']),
            'expired_at' => $this->faker->optional()->dateTimeBetween('now', '+2 years'),
        ];
    }
}
