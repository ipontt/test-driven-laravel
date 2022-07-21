<?php

use App\Models\Concert;
use App\Models\Order;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use function Pest\Laravel\get;

test('user can view their order confirmation', function () {
	$concert = Concert::factory()->create([
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

	$order = Order::factory()
		->hasAttached($concert, ['code' => 'TICKETCODE123'])
		->hasAttached($concert, ['code' => 'TICKETCODE456'])
		->create([
			'confirmation_number' => 'ORDERCONFIRMATION1234',
			'card_last_four' => 1881,
			'amount' => 8500,
			'email' => 'john@example.com',
		]);

	$response = get('orders/ORDERCONFIRMATION1234');

	$response
		->assertStatus(Response::HTTP_OK)
		->assertViewHas(key: 'order', value: $order)
		->assertSee('ORDERCONFIRMATION1234')
		->assertSee('$85.00')
		->assertSee('**** **** **** 1881')
		->assertSee('TICKETCODE123')
		->assertSee('TICKETCODE456')
		->assertSee('The Red Cord')
		->assertSee('with Animosity and Lethargy')
		->assertSee('The Mosh Pit')
		->assertSee('123 Example Lane')
		->assertSee('Laraville, ON')
		->assertSee('17916')
		->assertSee('john@example.com')
		->assertSee('2016-12-13 20:00:00');
});
