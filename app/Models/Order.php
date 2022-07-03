<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Support\LazyCollection;

class Order extends Model
{
	use HasFactory;

	protected $guarded = [];

	public static function forTickets(LazyCollection $tickets, string $email, int $amount): static
	{
		$order = static::create(['email' => $email, 'amount' => $amount]);

		$order->tickets()->saveMany($tickets);

		return $order;
	}

	/* RELATIONSHIPS */
	public function concert(): BelongsTo
	{
		return $this->belongsTo(Concert::class);
	}

	public function tickets(): HasMany
	{
		return $this->hasMany(Ticket::class);
	}

	/* METHODS */
	public function ticketQuantity(): int
	{
		return $this->tickets()->count();
	}
}
