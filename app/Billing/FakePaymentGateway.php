<?php

namespace App\Billing;

use App\Billing\Concerns\PaymentGateway;
use App\Billing\Exceptions\PaymentFailedException;
use Illuminate\Support\Collection;

class FakePaymentGateway implements PaymentGateway
{
	private Collection $charges;
	private $beforeFirstChargeCallback;

	public function __construct()
	{
		$this->charges = collect();
	}

	public function getValidTestToken(): string
	{
		return 'valid-token';
	}

	public function charge(int $amount, string $token): void
	{
		$this->handleCallbacks();

		throw_if(exception: PaymentFailedException::class, condition: $token !== $this->getValidTestToken(), message: 'Invalid Payment Token');

		$this->charges->push($amount);
	}

	public function totalCharges(): int
	{
		return $this->charges->sum();
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
