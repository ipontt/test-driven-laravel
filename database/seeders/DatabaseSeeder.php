<?php

namespace Database\Seeders;

use App\Models\Concert;
use App\Models\Ticket;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
	public function run(): void
	{
		Concert::factory()->published()->has(Ticket::factory()->count(10))->create([	
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
	}
}
