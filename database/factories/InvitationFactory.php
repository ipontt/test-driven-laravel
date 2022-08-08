<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invitation>
 */
class InvitationFactory extends Factory
{
	public function definition(): array
	{
		return [
			'code' => $this->faker->uuid(),
			'email' => $this->faker->unique()->email(),
		];
	}

	public function used(?User $user = null): static
	{
		return $this->for($user ?? User::factory());
	}

	public function unused(): static
	{
		return $this->state(fn (array $attributes) => ['user_id' => null]);
	}
}
