<?php

use App\Billing\Stripe\StripePaymentGateway;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Stripe\StripeClient;

uses()->group('integration');

beforeEach(function () {
	$this->stripe = new StripeClient(Config::get('services.stripe.secret'));
	$this->paymentGateway = new StripePaymentGateway(Config::get('services.stripe.secret'));
});

require dirname(__DIR__).'/PaymentGatewayContractTests.php';

test('90% of the payment is transfered to the destination account', function () {
	$this->paymentGateway->charge(
		amount: 5000,
		token: $this->paymentGateway->getValidTestToken(),
		destination_account_id: env('STRIPE_TEST_PROMOTER_ACCOUNT_ID'),
	);

	$lastCharge = Arr::first($this->stripe->charges->all(params: ['limit' => 1])['data']);

	expect($lastCharge)
		->amount->toEqual(5000)
		->and($lastCharge->transfer_data)
			->destination->toEqual(env('STRIPE_TEST_PROMOTER_ACCOUNT_ID'))
			->amount->toEqual(4500);
});
