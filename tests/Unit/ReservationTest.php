<?php

use App\Reservation;
use Illuminate\Support\LazyCollection;

it('can calculate the total cost', function () {
	$mockTickets = LazyCollection::make(function () {
		yield (object) ['price' => 1200];
		yield (object) ['price' => 1200];
		yield (object) ['price' => 1200];
	});

	expect(Reservation::for($mockTickets))->totalCost()->toBe(3600);
});
