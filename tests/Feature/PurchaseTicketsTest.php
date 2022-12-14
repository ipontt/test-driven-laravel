<?php

use App\Billing\Concerns\PaymentGateway;
use App\Billing\FakePaymentGateway;
use App\Mail\OrderConfirmationEmail;
use App\Models\Concert;
use App\Models\Ticket;
use App\Models\User;
use Facades\App\TicketCodeGenerator;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use function Pest\Laravel\postJson;

beforeEach(function () {
	Mail::fake();
	$this->paymentGateway = new FakePaymentGateway;
	app()->instance(
		abstract: PaymentGateway::class,
		instance: $this->paymentGateway
	);
});

test('customer can purchase tickets for published concerts', function () {
	$confirmation_number = (string) Str::freezeUuids();
	TicketCodeGenerator::shouldReceive('generateFor')->andReturn('TICKETCODE1', 'TICKETCODE2', 'TICKETCODE3');
	$user = User::factory()->create(['stripe_account_id' => 'test_stripe_account_id']);
	$concert = Concert::factory()->for($user)->published(ticket_quantity: 3)->create(['ticket_price' => 3250]);

	$response = postJson("/concerts/{$concert->id}/orders", [
		'email' => 'john@example.com',
		'ticket_quantity' => 3,
		'payment_token' => $this->paymentGateway->getValidTestToken(),
	]);

	$response
		->assertStatus(Response::HTTP_CREATED)
		->assertJson(fn (AssertableJson $json) =>
			$json->where('amount', 9750)
				->where('confirmation_number', $confirmation_number)
				->where('email', 'john@example.com')
				->where('tickets', fn (Collection $tickets) =>
					$tickets->sortBy('code')->values()->toJson() === json_encode([
						['code' => 'TICKETCODE1'],
						['code' => 'TICKETCODE2'],
						['code' => 'TICKETCODE3'],
					])
				)
		);
	expect($this->paymentGateway->totalChargesFor(account_id: 'test_stripe_account_id'))->toBe(9750);
	expect($concert)->hasOrderFor(email: 'john@example.com')->toBeTrue();
	$order = $concert->ordersFor(email: 'john@example.com')->first();
	expect($order)
		->not->toBeNull()
		->ticketQuantity()->toBe(3);

	Mail::assertSent(mailable: OrderConfirmationEmail::class, callback: function ($mail) use ($order) {
		return $mail->hasTo('john@example.com')
			&& $mail->order->id === $order->id;
	});
});

test('customer cannot purchase tickets for unpublished concerts', function () {
	$concert = Concert::factory()->has(Ticket::factory()->count(3))->unpublished()->create();

	$response = postJson("/concerts/{$concert->id}/orders", [
		'email' => 'john@example.com',
		'ticket_quantity' => 3,
		'payment_token' => $this->paymentGateway->getValidTestToken(),
	]);

	$response->assertStatus(Response::HTTP_NOT_FOUND);
	expect($this->paymentGateway->totalCharges())->toBe(0);
	expect($concert)->hasOrderFor(email: 'john@example.com')->toBeFalse();
});

test('customer cannot purchase tickets that another customer is trying to purchase', function () {
	$concert = Concert::factory()->published(ticket_quantity: 3)->create(['ticket_price' => 3250]);

	$this->paymentGateway->beforeFirstCharge(function ($paymentGateway) use ($concert){
		$response = postJson("/concerts/{$concert->id}/orders", [
			'email' => 'person_B_@example.com',
			'ticket_quantity' => 1,
			'payment_token' => $this->paymentGateway->getValidTestToken(),
		]);

		$response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
		expect($this->paymentGateway->totalCharges())->toBe(0);
		expect($concert)->hasOrderFor(email: 'person_B_@example.com')->toBeFalse();
	});

	$response = postJson("/concerts/{$concert->id}/orders", [
		'email' => 'person_A_@example.com',
		'ticket_quantity' => 3,
		'payment_token' => $this->paymentGateway->getValidTestToken(),
	]);

	expect($this->paymentGateway->totalCharges())->toBe(9750);
	expect($concert)->hasOrderFor(email: 'person_A_@example.com')->toBeTrue();
	expecT($concert->ordersFor(email: 'person_A_@example.com')->first())
		->not->toBeNull()
		->ticketQuantity()->toBe(3);
});

test('an order is not created if payment fails', function () {
	$concert = Concert::factory()->published(ticket_quantity: 3)->create(['ticket_price' => 3250]);

	$response = postJson("/concerts/{$concert->id}/orders", [
		'email' => 'john@example.com',
		'ticket_quantity' => 3,
		'payment_token' => 'invalid token',
	]);

	$response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
	expect($this->paymentGateway->totalCharges())->toBe(0);
	expect($concert)
		->hasOrderFor(email: 'john@example.com')->toBeFalse()
		->ticketsRemaining()->toBe(3);
});

test('cannot purchase more tickets than are available', function () {
	$concert = Concert::factory()->published(ticket_quantity: 50)->create();

	$response = postJson("/concerts/{$concert->id}/orders", [
		'email' => 'john@example.com',
		'ticket_quantity' => 51,
		'payment_token' => $this->paymentGateway->getValidTestToken(),
	]);

	$response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
	expect($this->paymentGateway->totalCharges())->toBe(0);
	expect($concert)->hasOrderFor(email: 'john@example.com')->toBeFalse();
	expect($concert->ticketsRemaining())->toBe(50);
});

test('an email is required to purchase tickets', function () {
	$concert = Concert::factory()->published(ticket_quantity: 3)->create();

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
	$concert = Concert::factory()->published(ticket_quantity: 3)->create();

	postJson("/concerts/{$concert->id}/orders", [
		'email' => 'john@example.com',
		'payment_token' => $this->paymentGateway->getValidTestToken(),
	])->assertJsonValidationErrorFor('ticket_quantity');
});

test('ticket quantity must be a positive integer. At least 1', function ($ticket_quantity) {
	$concert = Concert::factory()->published(ticket_quantity: 3)->create();

	postJson("/concerts/{$concert->id}/orders", [
		'email' => 'john@example.com',
		'ticket_quantity' => $ticket_quantity,
		'payment_token' => $this->paymentGateway->getValidTestToken(),
	])->assertJsonValidationErrorFor('ticket_quantity');
})->with([0, -1, 'not a number', 1.235, new StdClass]);

test('a payment token is required to purchase tickets', function () {
	$concert = Concert::factory()->published(ticket_quantity: 3)->create();

	postJson("/concerts/{$concert->id}/orders", [
		'email' => 'john@example.com',
		'ticket_quantity' => 3,
	])->assertJsonValidationErrorFor('payment_token');
});

test('the payment token must be valid to purchase tickets', function () {
	$concert = Concert::factory()->published(ticket_quantity: 3)->create();

	postJson("/concerts/{$concert->id}/orders", [
		'email' => 'john@example.com',
		'ticket_quantity' => 3,
		'payment_token' => 'invalid token',
	])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});
