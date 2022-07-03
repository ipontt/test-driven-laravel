<?php

use App\Models\Concert;
use App\Models\Ticket;

it('can be released from an Order associated to it', function (int $ticket_quantity) {
	$concert = Concert::factory()->create()->addTickets($ticket_quantity);
	$order = $concert->orderTickets(email: 'jane@example.com', ticket_quantity: $ticket_quantity);

	$order->tickets
		->each->release()
		->each(fn (Ticket $ticket) => expect($ticket)->order_id->toBeNull());
})->with([1, 2, 3, 4, 5]);

it('can be reserved', function () {
	$ticket = Ticket::factory()->for(Concert::factory())->create();
	expect($ticket)->reserved_at->toBeNull();

	$ticket->reserve();

	expect($ticket)->reserved_at->not->toBeNull();
});
