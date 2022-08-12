<?php

use App\Billing\FakePaymentGateway;
use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use App\Reservation;
use Illuminate\Support\LazyCollection;

it('can calculate the total cost', function () {
	$mockTickets = LazyCollection::make([
		(object) ['price' => 1200],
		(object) ['price' => 1200],
		(object) ['price' => 1200],
	]);

	expect(Reservation::for(tickets: $mockTickets, email: 'john@example.com'))->totalCost()->toBe(3600);
});

it('can retrieve the reservation tickets', function () {
	$mockTickets = LazyCollection::make([
		(object) ['price' => 1200],
		(object) ['price' => 1200],
		(object) ['price' => 1200],
	]);

	expect(Reservation::for(tickets: $mockTickets, email: 'john@example.com'))->tickets->toEqual($mockTickets);
});

it('can retrieve the reservation email')
	->expect(fn () => Reservation::for(tickets: LazyCollection::empty(), email: 'john@example.com'))
	->email->toBe('john@example.com');

it('releases the tickets when a reservation is cancelled', function () {
	$mockTickets = LazyCollection::make([
		Mockery::spy(Ticket::class),
		Mockery::spy(Ticket::class),
		Mockery::spy(Ticket::class),
	]);

	Reservation::for(tickets: $mockTickets, email: 'john@example.com')->cancel();

	$mockTickets->each->shouldHaveReceived('release');
});

it('creates an order when a reservation is completed', function () {
	$concert = Concert::factory()->create(['ticket_price' => 1000]);
	$tickets = Ticket::factory()->for($concert)->count(3)->create()->lazy();
	$reservation = Reservation::for(tickets: $tickets, email: 'john@example.com');
	$paymentGateway = new FakePaymentGateway;

	$order = $reservation->complete(
		paymentGateway: $paymentGateway,
		paymentToken: $paymentGateway->getValidTestToken(),
		destination_account_id: 'test_acc_1234',
	);

	expect($order)
		->toBeInstanceOf(Order::class)
		->amount->toBe(3000)
		->email->toBe('john@example.com')
		->ticketQuantity()->toBe(3)
		->and($paymentGateway)->totalChargesFor(account_id: 'test_acc_1234')->toBe(3000);
});
