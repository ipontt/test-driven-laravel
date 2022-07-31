<?php

namespace App\Billing\Stripe;

use App\Billing\Charge;
use App\Billing\Concerns\PaymentGateway;
use App\Billing\Exceptions\PaymentFailedException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Stripe\Charge as StripeCharge;
use Stripe\Exception\InvalidRequestException;
use Stripe\StripeClient;

use function collect;
use function date;

class StripePaymentGateway implements PaymentGateway
{
	private StripeClient $stripe;

	public const TEST_CARD_NUMBER = '4242424242424242';

	public function __construct(string $apiKey)
	{
		$this->stripe = new StripeClient($apiKey);
	}

	public function charge(int $amount, string $token): Charge
	{
		try {
			$stripeCharge = $this->stripe->charges->create(
				params: [
					'amount' => $amount,
					'source' => $token,
					'currency' => 'usd',
				],
			);
		} catch (InvalidRequestException $e) {
			throw new PaymentFailedException('Invalid Payment Token');
		}

		return new Charge(
			amount: $stripeCharge->amount,
			cardLastFour: $stripeCharge->source->last4,
		);
	}

	public function getValidTestToken(string $cardNumber = self::TEST_CARD_NUMBER): string
	{
		$token = $this->stripe->tokens->create(params: [
			'card' => [
				'number' => $cardNumber,
				'exp_month' => 1,
				'exp_year' => date('Y') + 1,
				'cvc' => 123,
			]
		]);

		return $token->id;
	}

	public function newChargesDuring(callable $callback): Collection
	{
		$latestCharge = $this->lastCharge();
		$callback($this);

		return $this->newChargesSince(charge: $latestCharge)
			->map(fn (StripeCharge $stripeCharge) => new Charge(
				amount: $stripeCharge->amount,
				cardLastFour: $stripeCharge->source->last4,
			))
			->values();
	}

	private function lastCharge(): ?StripeCharge
	{
		$charges = $this->stripe->charges->all(params: ['limit' => 1]);

		return Arr::first(array: $charges['data']);
	}

	private function newChargesSince(?StripeCharge $charge): Collection
	{
		$charges = $this->stripe->charges->all(params: ['ending_before' => $charge?->id]);

		return collect($charges['data']);
	}
}
