<?php

use App\Billing\Concerns\PaymentGateway;
use App\Billing\Exceptions\PaymentFailedException;

test('charges with a valid payment token are successful', function () {
	$newCharges = $this->paymentGateway->newChargesDuring(callback: function (PaymentGateway $paymentGateway) {
		$paymentGateway->charge(amount: 2500, token: $this->paymentGateway->getValidTestToken());
	});

	expect($newCharges)->sum->amount->toEqual(2500);
});

test('only one charge is created after a successful payment', function () {
	$newCharges = $this->paymentGateway->newChargesDuring(callback: function (PaymentGateway $paymentGateway) {
		$paymentGateway->charge(amount: 2500, token: $this->paymentGateway->getValidTestToken());
	});

	expect($newCharges)->toHaveCount(1);
});

it('can get details about a successful charge', function () {
	$charge = $this->paymentGateway->charge(
		amount: 2500,
		token: $this->paymentGateway->getValidTestToken(cardNumber: $this->paymentGateway::TEST_CARD_NUMBER)
	);

	expect($charge)
		->amount->toEqual(2500)
		->cardLastFour->toEqual(substr(string: $this->paymentGateway::TEST_CARD_NUMBER, offset: -4));
});

test('charges with an invalid payment token fail')
	->expect(fn () => $this->paymentGateway->charge(amount: 2500, token: 'invalid token'))
	->throws(PaymentFailedException::class, 'Invalid Payment Token');

test('no charges are made when using an invalid payment token', function () {
	$newCharges = $this->paymentGateway->newChargesDuring(callback: function (PaymentGateway $paymentGateway) {
		try {
			$paymentGateway->charge(amount: 2500, token: 'invalid token');

			$this->fail('Payment succeded with an invalid token');
		} catch (PaymentFailedException $e) {
			return;
		}
	});

	expect($newCharges)->toHaveCount(0);
});

it('can fetch charges created during a callback in descending order', function () {
	$this->paymentGateway->charge(amount: 3500, token: $this->paymentGateway->getValidTestToken());
	$this->paymentGateway->charge(amount: 2000, token: $this->paymentGateway->getValidTestToken());

	$newCharges = $this->paymentGateway->newChargesDuring(callback: function (PaymentGateway $paymentGateway) {
		$this->paymentGateway->charge(amount: 4000, token: $this->paymentGateway->getValidTestToken());
		$this->paymentGateway->charge(amount: 6000, token: $this->paymentGateway->getValidTestToken());
		$this->paymentGateway->charge(amount: 5500, token: $this->paymentGateway->getValidTestToken());
	});

	expect($newCharges->map->amount)
		->toHaveCount(3)
		->sum()->toEqual(15500)
		->all()->toBe([5500, 6000, 4000]);
});