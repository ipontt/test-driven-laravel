<?php

namespace App\Billing;

class Charge
{
	public function __construct(
		public readonly ?int $amount = null,
		public readonly ?string $cardLastFour = null,
		public readonly ?string $destination = null,
	) {}
}
