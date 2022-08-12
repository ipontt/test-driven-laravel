<?php

namespace Database\Seeders;

use App\Models\Concert;
use App\Models\Invitation;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
	public function run(): void
	{
		$user = User::factory()->create([
			'email' => 'a@q.cl',
			'password' => Hash::make(1234),
			'stripe_account_id' => null,
			'stripe_access_token' => null,
		]);

		Concert::factory()->for($user)->published(ticket_quantity: 10)->create([
			'title' => 'The Red Chord',
			'subtitle' => 'with Animosity and Lethargy',
			'ticket_price' => 3250,
			'venue' => 'The Mosh Pit',
			'venue_address' => '123 Example Lane',
			'city' => 'Laraville',
			'state' => 'ON',
			'zip' => '17916',
			'additional_information' => 'This concert is 19+.',
		]);

		Concert::factory()->for($user)->unpublished()->count(6)->create();
		Concert::factory()->for($user)->published()->count(6)->create();

		Concert::factory()->unpublished()->count(10)->create();
		Concert::factory()->published()->count(10)->create();

		Invitation::factory()->used(user: $user)->create();
		Invitation::factory()->unused()->create();
	}
}
