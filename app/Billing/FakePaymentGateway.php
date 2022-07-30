<?php

namespace App\Billing;

use App\Billing\Charge;
use App\Billing\Concerns\PaymentGateway;
use App\Billing\Exceptions\PaymentFailedException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FakePaymentGateway implements PaymentGateway
{
	private Collection $charges;
	private Collection $tokens;
	private $beforeFirstChargeCallback;

	public const TEST_CARD_NUMBER = '4242424242424242';

	public function __construct()
	{
		$this->charges = collect();
		$this->tokens = collect();
	}

	public function getValidTestToken(string $cardNumber = self::TEST_CARD_NUMBER): string
	{
		$token = 'fake-tok_' . Str::random(24);

		$this->tokens->put(key: $token, value: $cardNumber);

		return $token;
	}

	public function charge(int $amount, string $token): Charge
	{
		$this->handleCallbacks();

		\throw_if(exception: PaymentFailedException::class, condition: !$this->tokens->has($token), message: 'Invalid Payment Token');

		$charge = new Charge(
			amount: $amount,
			cardLastFour: substr(string: $this->tokens->pull($token), offset: -4),
		);

		$this->charges->push($charge);

		return $charge;
	}

	public function totalCharges(): int
	{
		return $this->charges->sum->amount;
	}

	public function beforeFirstCharge(callable $callback): void
	{
		$this->beforeFirstChargeCallback = $callback;
	}

	private function handleCallbacks(): void
	{
		if ($this->beforeFirstChargeCallback !== null) {
			$callback = $this->beforeFirstChargeCallback;
			$this->beforeFirstChargeCallback = null;
			$callback($this);
		}
	}

	public function newChargesDuring(callable $callback): Collection
	{
		$lastChargeIndex = $this->charges->count();
		$callback($this);

		return $this->charges->slice(offset: $lastChargeIndex)->reverse()->values();
	}
}
