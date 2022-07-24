<?php

use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use Facades\App\TicketCodeGenerator;

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

it('can be claimed for an order', function () {
	$order = Order::factory()->create();
	$ticket = Ticket::factory()->for(Concert::factory())->create(['code' => null]);
	TicketCodeGenerator::shouldReceive('generateFor', [$ticket])->andReturn('TICKETCODE1');

	$ticket->claimFor(order: $order);

	expect($order->tickets)
		->contains($ticket)->toBeTrue()
		->and($ticket)->code->toEqual('TICKETCODE1');
});
