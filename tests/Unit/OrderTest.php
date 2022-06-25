<?php

use App\Models\Concert;

test('tickets are released when an order is cancelled', function () {
	$concert = Concert::factory()->create()->addTickets(10);
	$order = $concert->orderTickets(email: 'jane@example.com', ticket_quantity: 5);

	$order->cancel();

	expect($concert)->ticketsRemaining()->toBe(10)
		->and($order)->exists->toBeFalse();
});
