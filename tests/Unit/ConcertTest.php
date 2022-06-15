<?php

use App\Models\Concert;
use Carbon\Carbon;

it('can get formatted date')
    ->expect(fn () => Concert::factory()->make(['date' => Carbon::parse('December 13, 2016 8:00pm')]))
    ->formatted_date->toBe('December 13, 2016');

it('can get formatted start time')
    ->expect(fn () => Concert::factory()->make(['date' => Carbon::parse('December 13, 2016 8:00pm')]))
    ->formatted_start_time->toBe('8:00pm');

it('can get ticket price in dollars')
    ->expect(fn () => Concert::factory()->make(['ticket_price' => 6750]))
    ->ticket_price_in_dollars->toBe('67.50');

test('concerts with a published_at date are published', function () {
    $publishedConcertA = Concert::factory()->create(['published_at' => Carbon::parse('-1 week')]);
    $publishedConcertB = Concert::factory()->create(['published_at' => Carbon::parse('-1 week')]);
    $unpublishedConcert = Concert::factory()->create(['published_at' => null]);

    $publishedConcerts = Concert::published()->get();

    expect($publishedConcerts)
        ->contains($publishedConcertA)->toBe(true)
        ->contains($publishedConcertB)->toBe(true)
        ->contains($unpublishedConcert)->toBe(false);
});

it('can order concert tickets', function () {
    $concert = Concert::factory()->create();

    $order = $concert->orderTickets(email: 'jane@example.com', ticket_quantity: 3);

    expect($order)
        ->email->toBe('jane@example.com')
        ->tickets->toHaveCount(3);
});
