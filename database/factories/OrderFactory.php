<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'confirmation_number' => $this->faker->uuid,
            'card_last_four' => (string) $this->faker->numberBetween(1000, 9999),
            'email' => $this->faker->email,
            'amount' => $this->faker->numberBetween(3000, 30000),
        ];
    }
}
