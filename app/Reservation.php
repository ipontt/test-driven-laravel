<?php

namespace App;

use Illuminate\Support\LazyCollection;

class Reservation
{
	public function __construct(private LazyCollection $tickets) {}

	public static function for(...$parameters): static
	{
		return new static(...$parameters);
	}

	public function totalCost(): int
	{
		return $this->tickets->sum('price');
	}

	public function cancel(): void
	{
		$this->tickets->each->release();
	}
}
