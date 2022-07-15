<?php

use App\Billing\Stripe\StripePaymentGateway;

uses()->group('integration');

beforeEach(function () {
	$this->paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));
});

require dirname(__DIR__).'/PaymentGatewayContractTests.php';
