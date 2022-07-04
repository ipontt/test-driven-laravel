<?php

namespace App;

use App\Billing\Concerns\PaymentGateway;
use App\Models\Order;
use Illuminate\Support\LazyCollection;

class Reservation
{
	public function __construct(
		private LazyCollection $tickets,
		private string $email,
	) {}

	public function tickets(): LazyCollection
	{
		return $this->tickets;
	}

	public function email(): string
	{
		return $this->email;
	}

	public static function for(...$parameters): static
	{
		return new static(...$parameters);
	}

	public function totalCost(): int
	{
		return $this->tickets->sum('price');
	}

	public function complete(PaymentGateway $paymentGateway, string $paymentToken): Order
	{
		$paymentGateway->charge(amount: $this->totalCost(), token: $paymentToken);

		return Order::forTickets(
			tickets: $this->tickets(),
			email: $this->email(),
			amount: $this->totalCost(),
		);
	}

	public function cancel(): void
	{
		$this->tickets->each->release();
	}
}
