<?php

namespace App\Billing\Concerns;

interface PaymentGateway
{
	public function charge(int $amount, string $token): void;
}