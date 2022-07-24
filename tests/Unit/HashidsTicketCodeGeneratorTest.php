<?php

use App\HashidsTicketCodeGenerator;
use App\Models\Ticket;

beforeEach(function () {
	$this->ticketCodeGenerator = new HashidsTicketCodeGenerator;
});

it('is at least 6 characters long', function () {
	$code = $this->ticketCodeGenerator->generateFor(ticket: Ticket::make(['id' => 1]));

	expect(strlen($code))->toBeGreaterThanOrEqual(6);
});

it('can only contain uppercase leters', function () {
	$code = $this->ticketCodeGenerator->generateFor(ticket: Ticket::make(['id' => 1]));

	expect($code)->toMatch('/^[A-Z]+$/');
});

it('generates the same code for the same ticket id', function () {
	$ticket = Ticket::make(['id' => 1]);
	$code1 = $this->ticketCodeGenerator->generateFor(ticket: $ticket);
	$code2 = $this->ticketCodeGenerator->generateFor(ticket: $ticket);

	expect($code1)->toEqual($code2);
});

it('generates different codes for different ticket ids', function () {
	$code1 = $this->ticketCodeGenerator->generateFor(ticket: Ticket::make(['id' => 1]));
	$code2 = $this->ticketCodeGenerator->generateFor(ticket: Ticket::make(['id' => 2]));

	expect($code1)->not->toEqual($code2);
});

it('generates different codes with different salts', function () {
	$generator1 = new HashidsTicketCodeGenerator(salt: 'salt1');
	$generator2 = new HashidsTicketCodeGenerator(salt: 'salt2');
	$ticket = Ticket::make(['id' => 1]);

	$code1 = $generator1->generateFor(ticket: $ticket);
	$code2 = $generator2->generateFor(ticket: $ticket);

	expect($code1)->not->toEqual($code2);
});
