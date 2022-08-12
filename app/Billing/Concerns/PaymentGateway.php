<?php

namespace App\Billing\Concerns;

use App\Billing\Charge;
use Illuminate\Support\Collection;

interface PaymentGateway
{
	const TEST_CARD_NUMBER = self::TEST_CARD_NUMBER;

	public function charge(int $amount, string $token, string $destination_account_id): Charge;

	public function getValidTestToken(string $cardNumber = self::TEST_CARD_NUMBER): string;

	public function newChargesDuring(callable $callback): Collection;
}