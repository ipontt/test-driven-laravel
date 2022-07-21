<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Pivot
{
	use HasFactory;

	protected $table = 'tickets';

	public $incrementing = 'true';

	protected $guarded = [];

	/* SCOPES */
	public function scopeAvailable(Builder $query): void
	{
		$query->whereNull('order_id')->whereNull('reserved_at');
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
		$this->update(['reserved_at' => null]);
	}

	public function reserve(): void
	{
		$this->update(['reserved_at' => now()]);
	}
}
