<?php

use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Http\Response;
use function Pest\Laravel\get;

test('user can view a published concert listing', function () {
	$concert = Concert::factory()->published()->create([
		'title' => 'The Red Cord',
		'subtitle' => 'with Animosity and Lethargy',
		'date' => Carbon::parse('December 13, 2016 8:00pm'),
		'ticket_price' => 3250,
		'venue' => 'The Mosh Pit',
		'venue_address' => '123 Example Lane',
		'city' => 'Laraville',
		'state' => 'ON',
		'zip' => '17916',
		'additional_information' => 'For tickets, call (555) 555-5555.',
	]);

	$response = get("/concerts/{$concert->id}");

	$response
		->assertStatus(Response::HTTP_OK)
		->assertSee('The Red Cord')
		->assertSee('with Animosity and Lethargy')
		->assertSee('December 13, 2016')
		->assertSee('8:00pm')
		->assertSee('32.50')
		->assertSee('The Mosh Pit')
		->assertSee('123 Example Lane')
		->assertSee('Laraville, ON 17916')
		->assertSee('For tickets, call (555) 555-5555.');
});

test('user cannot view an unpublished concert listing', function () {
	$concert = Concert::factory()->unpublished()->create();

	$response = get("/concerts/{$concert->id}");

	$response->assertStatus(Response::HTTP_NOT_FOUND);
});
