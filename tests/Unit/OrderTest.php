<?php

use App\Http\Resources\OrderResource;
use App\Models\Concert;

it('can be json serialized', function () {
	$concert = Concert::factory()->create(['ticket_price' => 1000])->addTickets(10);
	$order = $concert->orderTickets(email: 'jane@example.com', ticket_quantity: 5);

	$result = OrderResource::make($order)->jsonSerialize();

	expect($result)->toMatchArray([
		'email' => 'jane@example.com',
		'ticket_quantity' => 5,
		'amount' => 5000,
	]);
});

test('tickets are released when an order is cancelled', function () {
	$concert = Concert::factory()->create()->addTickets(10);
	$order = $concert->orderTickets(email: 'jane@example.com', ticket_quantity: 5);

	$order->cancel();

	expect($concert)->ticketsRemaining()->toBe(10)
		->and($order)->exists->toBeFalse();
});
