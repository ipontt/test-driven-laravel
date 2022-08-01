<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Concert>
 */
class ConcertFactory extends Factory
{
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
			'user_id' => User::factory(),
			'title' => $this->faker->sentence,
			'subtitle' => $this->faker->sentence,
			'date' => $this->faker->dateTimeBetween('+2 weeks', '+1 year')->format('Y-m-d H:00:00'),
			'ticket_price' => $this->faker->numberBetween(3000, 10000),
			'ticket_quantity' => $this->faker->numberBetween(5, 20),
			'venue' => $this->faker->sentence,
			'venue_address' => $this->faker->streetAddress,
			'city' => $this->faker->city,
			'state' => $this->faker->stateAbbr,
			'zip' => $this->faker->postCode,
			'additional_information' => $this->faker->text,
		];
	}

	public function published(?int $ticket_quantity = null): static
	{
		$ticket_quantity ??= $this->faker->numberBetween(5, 20);

		return $this->has(Ticket::factory()->count($ticket_quantity))->state(fn (array $attributes): array => [
			'ticket_quantity' => $ticket_quantity,
			'published_at' => $this->faker->dateTimeBetween('+1 week', '+2 weeks'),
		]);
	}

	public function unpublished(): static
	{
		return $this->state(fn (array $attributes) => [
			'published_at' => null,
		]);
	}
}
