<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
	use HasFactory;

	protected $guarded = [];

	/* SCOPES */
	public function scopeAvailable(Builder $query): void
	{
		$query->whereNull('order_id');
	}

	/* ATTRIBUTES */
	public function price(): Attribute
	{
		return Attribute::make(
			get: fn () => $this->concert->ticket_price,
		);
	}

	/* RELATIONSHIPS */
	public function concert(): BelongsTo
	{
		return $this->BelongsTo(Concert::class);
	}

	public function order(): BelongsTo
	{
		return $this->BelongsTo(Order::class);
	}

	/* METHODS */
	public function release(): void
	{
		$this->order()->dissociate()->save();
	}
}
