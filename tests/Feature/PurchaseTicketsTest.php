<?php

use App\Billing\Concerns\PaymentGateway;
use App\Billing\FakePaymentGateway;
use App\Models\Concert;
use function Pest\Laravel\postJson;

beforeEach(function () {
	$this->paymentGateway = new FakePaymentGateway;
	app()->instance(
		abstract: PaymentGateway::class,
		instance: $this->paymentGateway
	);
});

test('customer can purchase tickets for published concerts', function () {
	$concert = Concert::factory()->published()->create(['ticket_price' => 3250]);

	postJson("/concerts/{$concert->id}/orders", [
		'email' => 'john@example.com',
		'ticket_quantity' => 3,
		'payment_token' => $this->paymentGateway->getValidTestToken(),
	])->assertStatus(201);

	expect($this->paymentGateway->totalCharges())->toBe(3250 * 3);
	$order = $concert->orders()->where('email', 'john@example.com')->first();

	expect($order)
		->not->toBeNull()
		->tickets->toHaveCount(3);
});

test('customer cannot purchase tickets for unpublished concerts', function () {
	$concert = Concert::factory()->unpublished()->create();

	postJson("/concerts/{$concert->id}/orders", [
		'email' => 'john@example.com',
		'ticket_quantity' => 3,
		'payment_token' => $this->paymentGateway->getValidTestToken(),
	])->assertStatus(404);

	expect($concert)->orders->toHaveCount(0);
	expect($this->paymentGateway->totalCharges())->toBe(0);
});

test('an order is not created if payment fails', function () {
	$concert = Concert::factory()->published()->create(['ticket_price' => 3250]);

	postJson("/concerts/{$concert->id}/orders", [
		'email' => 'john@example.com',
		'ticket_quantity' => 3,
		'payment_token' => 'invalid token',
	])->assertStatus(422);

	expect($this->paymentGateway->totalCharges())->toBe(0);
	$order = $concert->orders()->where('email', 'john@example.com')->first();

	expect($order)->toBeNull();
});

test('an email is required to purchase tickets', function () {
	$concert = Concert::factory()->published()->create();

	postJson("/concerts/{$concert->id}/orders", [
		'ticket_quantity' => 3,
		'payment_token' => $this->paymentGateway->getValidTestToken(),
	])->assertJsonValidationErrorFor('email');
});

test('a valid email is required to purchase tickets', function ($email) {
	$concert = Concert::factory()->published()->create();

	postJson("/concerts/{$concert->id}/orders", [
		'email' => $email,
		'ticket_quantity' => 3,
		'payment_token' => $this->paymentGateway->getValidTestToken(),
	])->assertJsonValidationErrorFor('email');
})->with([null, 'not an email', 1, new StdClass]);

test('ticket quantity is required to purchase tickets', function () {
	$concert = Concert::factory()->published()->create();

	postJson("/concerts/{$concert->id}/orders", [
		'email' => 'john@example.com',
		'payment_token' => $this->paymentGateway->getValidTestToken(),
	])->assertJsonValidationErrorFor('ticket_quantity');
});

test('ticket quantity must be a positive integer. At least 1', function ($ticket_quantity) {
	$concert = Concert::factory()->published()->create();

	postJson("/concerts/{$concert->id}/orders", [
		'email' => 'john@example.com',
		'ticket_quantity' => $ticket_quantity,
		'payment_token' => $this->paymentGateway->getValidTestToken(),
	])->assertJsonValidationErrorFor('ticket_quantity');
})->with([0, -1, 'not a number', 1.235, new StdClass]);

test('a payment token is required to purchase tickets', function () {
	$concert = Concert::factory()->published()->create();

	postJson("/concerts/{$concert->id}/orders", [
		'email' => 'john@example.com',
		'ticket_quantity' => 3,
	])->assertJsonValidationErrorFor('payment_token');
});

test('the payment token must be valid to purchase tickets', function () {
	$concert = Concert::factory()->published()->create();

	postJson("/concerts/{$concert->id}/orders", [
		'email' => 'john@example.com',
		'ticket_quantity' => 3,
		'payment_token' => 'invalid token',
	])->assertStatus(422);
});
