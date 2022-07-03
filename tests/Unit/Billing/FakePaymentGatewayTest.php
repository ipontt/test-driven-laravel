<?php

use App\Billing\Exceptions\PaymentFailedException;
use App\Billing\FakePaymentGateway;

beforeEach(function () {
	$this->paymentGateway = new FakePaymentGateway;
});

test('charges with a valid payment token are successful')
	->tap(fn () => $this->paymentGateway->charge(amount: 2500, token: $this->paymentGateway->getValidTestToken()))
	->expect(fn () => $this->paymentGateway->totalCharges())
	->toBe(2500);

test('charges with an invalid payment token fail')
	->tap(fn () => $this->paymentGateway->charge(amount: 2500, token: 'invalid token'))
	->throws(PaymentFailedException::class, 'Invalid Payment Token');

it('can run a hook before the first charge occurs', function () {
	$times_callback_was_run = 0;
	$this->paymentGateway->beforeFirstCharge(function ($paymentGateway) use (&$times_callback_was_run) {
		$this->paymentGateway->charge(amount: 2500, token: $this->paymentGateway->getValidTestToken());
		expect($paymentGateway)->totalCharges()->toBe(2500);
		$times_callback_was_run++;
	});

	$this->paymentGateway->charge(amount: 2500, token: $this->paymentGateway->getValidTestToken());
	expect($times_callback_was_run)->toBe(1);
	expect($this->paymentGateway)->totalCharges()->toBe(5000);
});