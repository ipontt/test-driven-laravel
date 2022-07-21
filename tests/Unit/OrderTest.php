<?php

use App\Http\Resources\OrderResource;
use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use App\Reservation;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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

it('can get the amount in dollars')
	->expect(fn () => Order::factory()->make(['amount' => 6750]))
	->amount_in_dollars->toBe('67.50');

it('can get the masked card number')
	->expect(fn () => Order::factory()->make(['card_last_four' => '4242']))
	->maked_card_number->toBe('**** **** **** 4242');

it('can be retrieved by its confirmation number', function () {
	$order = Order::factory()->create(['confirmation_number' => 'CONFIRMATION123']);

	expect(Order::findByConfirmationNumber('CONFIRMATION123'))
		->toBeInstanceOf(Order::class)
		->id->toEqual($order->id);
});

it('throws an exception when retrieving a non-existent order by its confirmation number')
	->tap(fn () => Order::findByConfirmationNumber('NONEXISTENT'))
	->throws(ModelNotFoundException::class);

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
