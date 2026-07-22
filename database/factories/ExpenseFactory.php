<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'amount' => $this->faker->randomFloat(2, 50, 5000),
            'date' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'recurrence' => $this->faker->randomElement(['monthly', 'quarterly', 'semiannual', 'yearly', 'one_time']),
            'category' => $this->faker->randomElement(['software', 'infrastructure', 'marketing', 'operations', 'other']),
            'status' => $this->faker->randomElement(['pending', 'paid']),
        ];
    }
}
