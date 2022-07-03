<?php

use App\Models\Concert;
use App\Models\Ticket;

it('can be reserved', function () {
	$ticket = Ticket::factory()->for(Concert::factory())->create();
	expect($ticket)->reserved_at->toBeNull();

	$ticket->reserve();

	expect($ticket)->reserved_at->not->toBeNull();
});

it('can be released', function () {
	$ticket = Ticket::factory()->for(Concert::factory())->reserved()->create();
	expect($ticket)->reserved_at->not->toBeNull();

	$ticket->release();

	expect($ticket)->reserved_at->toBeNull();
});
