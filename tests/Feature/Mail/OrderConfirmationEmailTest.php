<?php

use App\Mail\OrderConfirmationEmail;
use App\Models\Order;

beforeEach(function () {
	$this->order = Order::factory()->create();
	$this->mailable = new OrderConfirmationEmail(order: $this->order);
});

it('contains a link to the order confirmation page', function () {
	$this->mailable->assertSeeInHtml(\route('orders.show', [$this->order]));
});

it('has a subject', function () {
	$this->mailable->build();
	
	expect($this->mailable)->hasSubject('Your TicketBeast Order')->toBeTrue();
});
