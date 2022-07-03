<?php

use App\Http\Resources\OrderResource;
use App\Models\Concert;
use App\Models\Order;

it('can be created from tickets, email and amount', function () {
	$concert = Concert::factory()->create()->addTickets(10);
	expect($concert)->ticketsRemaining()->toBe(10);

	$order = Order::forTickets(tickets: $concert->findTickets(3), email: 'john@example.com', amount: 3000);

	expect($concert)->ticketsRemaining()->toBe(7);
	expect($order)
		->amount->toBe(3000)
		->email->toBe('john@example.com')
		->ticketQuantity()->toBe(3);
});

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
