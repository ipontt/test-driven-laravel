<?php

namespace App\Billing\Concerns;

use Illuminate\Support\Collection;

interface PaymentGateway
{
	public function charge(int $amount, string $token): void;

	public function getValidTestToken(): string;

	public function newChargesDuring(callable $callback): Collection;
}