<?php

use App\Billing\FakePaymentGateway;

beforeEach(function () {
	$this->paymentGateway = new FakePaymentGateway;
});

require __DIR__.'/PaymentGatewayContractTests.php';

it('can run a hook before the first charge occurs', function () {
	$times_callback_was_run = 0;
	$this->paymentGateway->beforeFirstCharge(function ($paymentGateway) use (&$times_callback_was_run) {
		$this->paymentGateway->charge(
			amount: 2500,
			token: $this->paymentGateway->getValidTestToken(),
			destination_account_id: 'test_acc_1234',
		);
		expect($paymentGateway)->totalCharges()->toBe(2500);
		$times_callback_was_run++;
	});

	$this->paymentGateway->charge(
		amount: 2500,
		token: $this->paymentGateway->getValidTestToken(),
		destination_account_id: 'test_acc_1234',
	);
	expect($times_callback_was_run)->toBe(1);
	expect($this->paymentGateway)->totalCharges()->toBe(5000);
});

it('can get total charges for a specific account', function () {
	$this->paymentGateway->charge(
		amount: 1000,
		token: $this->paymentGateway->getValidTestToken(),
		destination_account_id: 'test_acc_0000'
	);
	$this->paymentGateway->charge(
		amount: 2500,
		token: $this->paymentGateway->getValidTestToken(),
		destination_account_id: 'test_acc_1234'
	);
	$this->paymentGateway->charge(
		amount: 4000,
		token: $this->paymentGateway->getValidTestToken(),
		destination_account_id: 'test_acc_1234'
	);

	expect($this->paymentGateway)->totalChargesFor(account_id: 'test_acc_1234')->toEqual(6500);
});
