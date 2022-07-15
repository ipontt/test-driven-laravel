<?php

namespace App\Billing\Stripe;

use App\Billing\Concerns\PaymentGateway;
use App\Billing\Exceptions\PaymentFailedException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Stripe\Charge;
use Stripe\Exception\InvalidRequestException;
use Stripe\StripeClient;

class StripePaymentGateway implements PaymentGateway
{
	private StripeClient $stripe;

	public function __construct(string $apiKey)
	{
		$this->stripe = new StripeClient($apiKey);
	}

	public function charge(int $amount, string $token): void
	{
		try {
			$this->stripe->charges->create(
				params: [
					'amount' => $amount,
					'source' => $token,
					'currency' => 'usd',
				],
			);
		} catch (InvalidRequestException $e) {
			throw new PaymentFailedException('Invalid Payment Token');
		}
	}

	public function getValidTestToken(): string
	{
		$token = $this->stripe->tokens->create(params: [
			'card' => [
				'number' => 4242_4242_4242_4242,
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

		return $this->newChargesSince(charge: $latestCharge)->pluck('amount')->values();
	}

	private function lastCharge(): ?Charge
	{
		$charges = $this->stripe->charges->all(params: ['limit' => 1]);

		return Arr::first(array: $charges['data']);
	}

	private function newChargesSince(?Charge $charge): Collection
	{
		$charges = $this->stripe->charges->all(params: ['ending_before' => $charge?->id]);

		return collect($charges['data']);
	}
}

/*
// cURL implementation
class StripePaymentGateway implements PaymentGateway
{
	public function __construct(private string $apiKey) {}

	public function charge(int $amount, string $token): void
	{
		$curlHandle = curl_init();

		curl_setopt_array(handle: $curlHandle, options: [
			CURLOPT_URL => 'https://api.stripe.com/v1/charges',
			CURLOPT_USERNAME => config('services.stripe.secret'),
			CURLOPT_POSTFIELDS => http_build_query([
				'amount' => $amount,
				'currency' => 'usd',
				'source' => $token
			]),
			CURLOPT_RETURNTRANSFER => true
		]);

		if (($response = curl_exec(handle: $curlHandle)) === false) {
			throw new PaymentFailedException('Failed to connect with Stripe');
		}

		curl_close(handle: $curlHandle);

		$response = json_decode($response);

		if (curl_getinfo(handle: $curlHandle, option: CURLINFO_HTTP_CODE) === 400
			&& $response?->error?->type === 'invalid_request_error'
			&& in_array($response?->error?->message, ["No such token: '$token'", "You cannot use a Stripe token more than once: $token."])
		) {
			throw new PaymentFailedException('Invalid Payment Token');
		}
	}
}

// Guzzle Implementation (Http Facade)
use Illuminate\Support\Facades\Http;

class StripePaymentGateway implements PaymentGateway
{
	public function __construct(private string $apiKey) {}

	public function charge(int $amount, string $token): void
	{
		$response = Http::asForm()
			->withToken(config('services.stripe.secret'))
			->post('https://api.stripe.com/v1/charges', [
				'amount' => $amount,
				'currency' => 'usd',
				'source' => $token
			]);

		$body = $response->object();

		if ($response->status() === 400
			&& $body?->error?->type === 'invalid_request_error'
			&& in_array($body?->error?->message, ["No such token: '$token'", "You cannot use a Stripe token more than once: $token."])
		) {
			throw new PaymentFailedException('Invalid Payment Token');
		}			
	}
}
*/
