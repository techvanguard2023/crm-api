<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'company_name' => $this->faker->optional()->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'type' => $this->faker->randomElement(['PF', 'PJ']),
            'document' => $this->faker->unique()->numerify('###########'),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'zip_code' => $this->faker->postcode(),
            'country' => 'Brazil',
        ];
    }
}
