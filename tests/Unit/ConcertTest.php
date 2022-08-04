<?php

use App\Exceptions\ConcertAlreadyPublishedException;
use App\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Support\Facades\Date;

it('can get formatted date')
	->expect(fn () => Concert::factory()->make(['date' => Date::parse('December 13, 2016 8:00pm')]))
	->formatted_date->toEqual('December 13, 2016');

it('can get formatted start time')
	->expect(fn () => Concert::factory()->make(['date' => Date::parse('December 13, 2016 8:00pm')]))
	->formatted_start_time->toEqual('8:00pm');

it('can get ticket price in dollars')
	->expect(fn () => Concert::factory()->make(['ticket_price' => 6750]))
	->ticket_price_in_dollars->toEqual('67.50');

test('concerts with a published_at date are published', function () {
	$publishedConcertA = Concert::factory()->create(['published_at' => Date::parse('-1 week')]);
	$publishedConcertB = Concert::factory()->create(['published_at' => Date::parse('-1 week')]);
	$unpublishedConcert = Concert::factory()->create(['published_at' => null]);

	$publishedConcerts = Concert::published()->get();

	expect($publishedConcerts)
		->contains($publishedConcertA)->toBeTrue()
		->contains($publishedConcertB)->toBeTrue()
		->contains($unpublishedConcert)->toBeFalse();
});

it('can be published', function () {
	$concert = Concert::factory()->create([
		'ticket_quantity' => 10,
		'published_at' => null,
	]);
	expect($concert)
		->ticketsRemaining()->toEqual(0)
		->isPublished()->toBeFalse();

	$concert->publish();

	expect($concert->fresh())
		->ticketsRemaining()->toEqual(10)
		->isPublished()->toBeTrue();
});

it('throws an exception when trying to publish an already published concert', function () {
	$concert = Concert::factory()->published()->create();

	$concert->publish();
})->throws(ConcertAlreadyPublishedException::class);

test('tickets remaining does not include tickets associated with an order', function () {
	$concert = Concert::factory()->create();
	$concert->tickets()->saveMany([
		...Ticket::factory()->for(Order::factory())->count(2)->make(),
		...Ticket::factory()->count(4)->make(),
	]);

	expect($concert)->ticketsRemaining()->toEqual(4);
});

test('tickets sold only include tickets associated with an order', function () {
	$concert = Concert::factory()->create();
	$concert->tickets()->saveMany([
		...Ticket::factory()->for(Order::factory())->count(2)->make(),
		...Ticket::factory()->count(4)->make(),
	]);

	expect($concert)->ticketsSold()->toEqual(2);
});

test('total tickets include all tickets associated with an order', function () {
	$concert = Concert::factory()->create();
	$concert->tickets()->saveMany([
		...Ticket::factory()->for(Order::factory())->count(2)->make(),
		...Ticket::factory()->count(4)->make(),
	]);

	expect($concert)->totalTickets()->toEqual(6);
});

it('can calculate the percentage of tickets sold', function () {
	$concert = Concert::factory()->create();
	$concert->tickets()->saveMany([
		...Ticket::factory()->for(Order::factory())->count(2)->make(),
		...Ticket::factory()->count(5)->make(),
	]);

	// 2 / 7 = 0,285714286
	expect($concert)->percentSoldOut()->toEqual(28.57);
});

it('can calculate the revenue in dollars', function () {
	$concert = Concert::factory()->create();
	$orderA = Order::factory()->create(['amount' => 9625]);
	$orderB = Order::factory()->create(['amount' => 3850]);
	$concert->tickets()->saveMany([
		...Ticket::factory()->for($orderA)->count(2)->make(),
		Ticket::factory()->for($orderB)->make(),
		...Ticket::factory()->count(5)->make(),
	]);

	// 3850 + 9625 = 13475 cents = 134.75 usd
	expect($concert)->revenueInDollars()->toEqual(134.75);

});

it('can reserve available tickets', function () {
	$concert = Concert::factory()->published(ticket_quantity: 3)->create();
	expect($concert)->ticketsRemaining()->toEqual(3);

	$reservation = $concert->reserveTickets(quantity: 2, email: 'john@example.com');

	expect($reservation)
		->tickets->toHaveCount(2)
		->email->toEqual('john@example.com')
		->and($concert->fresh())->ticketsRemaining()->toEqual(1);
});

it('cannot reserve tickets that have already been reserved', function () {
	$concert = Concert::factory()->published(ticket_quantity: 10)->create();
	$concert->reserveTickets(quantity: 8, email: 'jane@example.com');

	expect(fn () => $concert->reserveTickets(quantity: 3, email: 'john@example.com'))
		->toThrow(NotEnoughTicketsException::class)
		->and($concert)->ticketsRemaining()->toEqual(2);
});

it('cannot reserve tickets that have already been purchased', function () {
	$concert = Concert::factory()->published(ticket_quantity: 10)->create();
	$order = Order::factory()->create()->tickets()->saveMany($concert->tickets->take(8));

	expect(fn () => $concert->reserveTickets(quantity: 3, email: 'john@example.com'))
		->toThrow(NotEnoughTicketsException::class)
		->and($concert)->ticketsRemaining()->toEqual(2);
});

test('trying to reserve more tickets than are available triggers an exception', function () {
	$concert = Concert::factory()->published(ticket_quantity: 10)->create();

	expect(fn () => $concert->reserveTickets(quantity: 11, email: 'jane@example.com'))
		->toThrow(NotEnoughTicketsException::class)
		->and($concert)->hasOrderFor(email: 'jane@example.com')->toBeFalse();
});
