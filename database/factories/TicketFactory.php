<?php

namespace Database\Factories;

use App\Models\Concert;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
			'concert_id' => Concert::factory(),
		];
	}

	public function reserved(): static
	{
		return $this->state(function (array $attributes) {
			return [
				'reserved_at' => $this->faker->dateTime(),
			];
		});
	}
}
