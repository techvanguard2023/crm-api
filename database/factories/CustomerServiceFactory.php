<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerServiceFactory extends Factory
{
    protected $table = 'customer_service';

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-6 months', 'now');
        $nextDueDate = $this->faker->dateTimeBetween('now', '+1 year');

        return [
            'customer_id' => Customer::factory(),
            'service_id' => Service::factory(),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'recurrence' => $this->faker->randomElement(['monthly', 'quarterly', 'semiannual', 'yearly', 'one_time']),
            'start_date' => $startDate,
            'next_due_date' => $nextDueDate,
            'domain_id' => null,
        ];
    }
}
