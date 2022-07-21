<?php

namespace App\Models;

use App\Reservation;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;

class Order extends Model
{
	use HasFactory;

	protected $guarded = [];

	public static function forTickets(LazyCollection $tickets, string $email, int $amount): static
	{
		$order = static::create([
			'confirmation_number' => Str::uuid(),
			'email' => $email,
			'amount' => $amount
		]);

		$order->tickets()->saveMany($tickets);

		return $order;
	}

	/* ATTRIBUTES */
	public function amountInDollars(): Attribute
	{
		return Attribute::make(
			get: fn () => number_format(num: $this->amount / 100, decimals: 2),
		);
	}

	public function makedCardNumber(): Attribute
	{
		return Attribute::make(
			get: fn () => wordwrap(
				string: Str::padLeft(value: $this->card_last_four, length: 16, pad: '*'),
				width: 4,
				break: ' ',
				cut_long_words: true,
			),
		);
	}

	/* RELATIONSHIPS */
	public function concerts(): BelongsToMany
	{
		return $this->belongsToMany(Concert::class, 'tickets')->using(Ticket::class)->as('ticket')->withPivot('reserved_at');
	}

	public function tickets(): HasMany
	{
		return $this->hasMany(Ticket::class);
	}

	/* METHODS */
	public static function findByConfirmationNumber(string $confirmation_number): static
	{
		return static::where('confirmation_number', $confirmation_number)->firstOrFail();
	}

	public function ticketQuantity(): int
	{
		return $this->tickets()->count();
	}
}
