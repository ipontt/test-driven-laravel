<?php

use App\Models\Ticket;
use App\Reservation;
use Illuminate\Support\LazyCollection;

it('can calculate the total cost', function () {
	$mockTickets = LazyCollection::make(function () {
		yield (object) ['price' => 1200];
		yield (object) ['price' => 1200];
		yield (object) ['price' => 1200];
	});

	expect(Reservation::for(tickets: $mockTickets))->totalCost()->toBe(3600);
});

it('releases the tickets when a reservation is cancelled', function () {
	$mockTickets = LazyCollection::make([
		Mockery::spy(Ticket::class),
		Mockery::spy(Ticket::class),
		Mockery::spy(Ticket::class),
	]);

	Reservation::for(tickets: $mockTickets)->cancel();

	$mockTickets->each(fn ($spy) => $spy->shouldHaveReceived('release'));
});
