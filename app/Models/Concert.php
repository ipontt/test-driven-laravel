<?php

namespace App\Models;

use App\Exceptions\ConcertAlreadyPublishedException;
use App\Exceptions\NotEnoughTicketsException;
use App\Reservation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\LazyCollection;

use function number_format;
use function tap;
use function throw_if;

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
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	public function orders(): BelongsToMany
	{
		return $this->belongsToMany(Order::class, 'tickets')->using(Ticket::class)->as('ticket')->withPivot('reserved_at', 'code');
	}

	public function tickets(): HasMany
	{
		return $this->hasMany(Ticket::class);
	}

	/* METHODS */
	public function isPublished(): bool
	{
		return $this->published_at !== null;
	}

	public function publish(): self
	{
		throw_if(exception: ConcertAlreadyPublishedException::class, condition: $this->isPublished());

		return tap($this, function (Concert $concert) {
			$this->addTickets($this->ticket_quantity);
			$this->update(['published_at' => $this->freshTimestamp()]);
		});
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

	private function addTickets(int $quantity): self
	{
		Ticket::factory()->for($this)->count($quantity)->create();

		return $this;
	}

	public function ticketsRemaining(): int
	{
		return $this->tickets_remaining ?? $this->loadCount([
			'tickets as tickets_remaining' => fn (Builder $tickets) => $tickets->available(),
		])->tickets_remaining;
	}

	public function ticketsSold(): int
	{
		return $this->tickets_sold ?? $this->loadCount([
			'tickets as tickets_sold' => fn (Builder $tickets) => $tickets->sold(),
		])->tickets_sold;
	}

	public function totalTickets(): int
	{
		return $this->total_tickets ?? $this->loadCount([
			'tickets as total_tickets',
		])->total_tickets;
	}

	public function percentSoldOut(): float
	{
		return number_format(num: 100 * $this->ticketsSold() / $this->totalTickets(), decimals: 2);
	}

	public function revenueInDollars(): float
	{
		return $this->orders()->cursor()->unique('id')->sum('amount') / 100;
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
