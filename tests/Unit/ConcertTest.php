<?php

use App\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Support\Facades\Date;

it('can get formatted date')
	->expect(fn () => Concert::factory()->make(['date' => Date::parse('December 13, 2016 8:00pm')]))
	->formatted_date->toBe('December 13, 2016');

it('can get formatted start time')
	->expect(fn () => Concert::factory()->make(['date' => Date::parse('December 13, 2016 8:00pm')]))
	->formatted_start_time->toBe('8:00pm');

it('can get ticket price in dollars')
	->expect(fn () => Concert::factory()->make(['ticket_price' => 6750]))
	->ticket_price_in_dollars->toBe('67.50');

test('concerts with a published_at date are published', function () {
	$publishedConcertA = Concert::factory()->create(['published_at' => Date::parse('-1 week')]);
	$publishedConcertB = Concert::factory()->create(['published_at' => Date::parse('-1 week')]);
	$unpublishedConcert = Concert::factory()->create(['published_at' => null]);

	$publishedConcerts = Concert::published()->get();

	expect($publishedConcerts)
		->contains($publishedConcertA)->toBe(true)
		->contains($publishedConcertB)->toBe(true)
		->contains($unpublishedConcert)->toBe(false);
});

it('can be published', function () {
	$concert = Concert::factory()->create(['published_at' => null]);
	expect($concert)->isPublished()->toBeFalse();

	$concert->publish();

	expect($concert)->isPublished()->toBeTrue();
});

it('can add tickets', function () {
	$concert = Concert::factory()->create();

	$concert->addTickets(50);

	expect($concert)->ticketsRemaining()->toBe(50);
});

test('tickets remaining does not include tickets associated with an order', function () {
	$concert = Concert::factory()->create();
	$concert->tickets()->saveMany([
		...Ticket::factory()->for(Order::factory())->count(20)->make(),
		...Ticket::factory()->count(40)->make(),
	]);

	expect($concert)->ticketsRemaining()->toBe(40);
});

it('can reserve available tickets', function () {
	$concert = Concert::factory()->create()->addTickets(3);
	expect($concert)->ticketsRemaining()->toBe(3);

	$reservation = $concert->reserveTickets(quantity: 2, email: 'john@example.com');

	expect($reservation)
		->tickets->toHaveCount(2)
		->email->toBe('john@example.com')
		->and($concert)->ticketsRemaining()->toBe(1);
});

it('cannot reserve tickets that have already been reserved', function () {
	$concert = Concert::factory()->create()->addTickets(10);
	$concert->reserveTickets(quantity: 8, email: 'jane@example.com');

	expect(fn () => $concert->reserveTickets(quantity: 3, email: 'john@example.com'))
		->toThrow(NotEnoughTicketsException::class)
		->and($concert)->ticketsRemaining()->toBe(2);
});

it('cannot reserve tickets that have already been purchased', function () {
	$concert = Concert::factory()->create()->addTickets(10);
	$order = Order::factory()->create()->tickets()->saveMany($concert->tickets->take(8));

	expect(fn () => $concert->reserveTickets(quantity: 3, email: 'john@example.com'))
		->toThrow(NotEnoughTicketsException::class)
		->and($concert)->ticketsRemaining()->toBe(2);
});

test('trying to reserve more tickets than are available triggers an exception', function () {
	$concert = Concert::factory()->create()->addTickets(10);

	expect(fn () => $concert->reserveTickets(quantity: 11, email: 'jane@example.com'))
		->toThrow(NotEnoughTicketsException::class)
		->and($concert)->hasOrderFor(email: 'jane@example.com')->toBeFalse();
});
