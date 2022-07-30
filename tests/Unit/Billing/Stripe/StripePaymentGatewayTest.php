<?php

use App\Billing\Stripe\StripePaymentGateway;
use Illuminate\Support\Facades\Config;

uses()->group('integration');

beforeEach(function () {
	$this->paymentGateway = new StripePaymentGateway(Config::get('services.stripe.secret'));
});

require \dirname(__DIR__).'/PaymentGatewayContractTests.php';
