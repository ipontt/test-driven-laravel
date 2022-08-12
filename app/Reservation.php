<?php

namespace App;

use App\Billing\Concerns\PaymentGateway;
use App\Models\Order;
use Illuminate\Support\LazyCollection;

class Reservation
{
	public function __construct(
		public readonly LazyCollection $tickets,
		public readonly string $email,
	) {}

	public static function for(...$parameters): static
	{
		return new static(...$parameters);
	}

	public function totalCost(): int
	{
		return $this->tickets->sum('price');
	}

	public function complete(PaymentGateway $paymentGateway, string $paymentToken, string $destination_account_id): Order
	{
		return Order::forTickets(
			tickets: $this->tickets,
			email: $this->email,
			charge: $paymentGateway->charge(
				amount: $this->totalCost(),
				token: $paymentToken,
				destination_account_id: $destination_account_id,
			),
		);
	}

	public function cancel(): void
	{
		$this->tickets->each->release();
	}
}
