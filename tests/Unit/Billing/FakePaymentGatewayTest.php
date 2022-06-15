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