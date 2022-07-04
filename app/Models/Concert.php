<?php

namespace App\Models;

use App\Exceptions\NotEnoughTicketsException;
use App\Reservation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\LazyCollection;

class Concert extends Model
{
	use HasFactory;

	protected $guarded = [];
	protected $dates = ['date'];

	/* SCOPES */
	public function scopePublished(Builder $query): void
	{
		$query->whereNotNull('published_at');
	}

	/* ATTRIBUTES */
	public function formattedDate(): Attribute
	{
		return Attribute::make(
			get: fn () => $this->date->format('F d, Y'),
		);
	}

	public function formattedStartTime(): Attribute
	{
		return Attribute::make(
			get: fn () => $this->date->format('g:ia'),
		);
	}

	public function ticketPriceInDollars(): Attribute
	{
		return Attribute::make(
			get: fn () => number_format(num: $this->ticket_price / 100, decimals: 2),
		);
	}

	/* RELATIONSHIPS */
	public function orders(): BelongsToMany
	{
		return $this->belongsToMany(Order::class, 'tickets');
	}

	public function tickets(): HasMany
	{
		return $this->hasMany(Ticket::class);
	}

	/* METHODS */
	public function orderTickets(string $email, int $ticket_quantity): Order
	{
		$tickets = $this->findTickets($ticket_quantity);

		return $this->createOrder($email, $tickets);
	}

	public function reserveTickets(int $quantity, string $email): Reservation
	{
		$tickets = $this->findTickets($quantity)->each->reserve();

		return Reservation::for(tickets: $tickets, email: $email);
	}

	public function findTickets(int $quantity): LazyCollection
	{
		$tickets = $this->tickets()->available()->limit($quantity)->cursor()->remember();

		throw_if(exception: NotEnoughTicketsException::class, condition: $quantity > $tickets->count());

		return $tickets;
	}

	public function createOrder(string $email, LazyCollection $tickets): Order
	{
		return Order::forTickets(tickets: $tickets, email: $email, amount: $tickets->sum('price'));
	}

	public function addTickets(int $quantity): self
	{
		Ticket::factory()->for($this)->count($quantity)->create();

		return $this;
	}

	public function ticketsRemaining(): int
	{
		return $this->tickets()->available()->count();
	}

	public function hasOrderFor(string $email): bool
	{
		return $this->orders()->where('email', $email)->exists();
	}

	public function ordersFor(string $email): LazyCollection
	{
		return $this->orders()->where('email', $email)->cursor();
	}
}
