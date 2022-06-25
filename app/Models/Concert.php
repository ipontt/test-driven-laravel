<?php

namespace App\Models;

use App\Exceptions\NotEnoughTicketsException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
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
    public function FormattedDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->date->format('F d, Y'),
        );
    }

    public function FormattedStartTime(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->date->format('g:ia'),
        );
    }

    public function TicketPriceInDollars(): Attribute
    {
        return Attribute::make(
            get: fn () => number_format($this->ticket_price / 100, 2),
        );
    }

    /* RELATIONSHIPS */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /* METHODS */
    public function orderTickets(string $email, int $ticket_quantity): Order
    {
        $tickets = $this->tickets()->available()->limit($ticket_quantity)->cursor();

        throw_if(exception: NotEnoughTicketsException::class, condition: $ticket_quantity > $tickets->count());

        $order = $this->orders()->create(['email' => $email]);

        $order->tickets()->saveMany($tickets);

        return $order;
    }

    public function addTickets(int $quantity): self
    {
        for ($i = 0; $i < $quantity; $i++)
            $this->tickets()->create();

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

    public function ordersFor(string $email, bool $lazy = true): Collection|LazyCollection
    {
        return $this->orders()
            ->where('email', $email)
            ->when($lazy,
                fn (Builder $query) => $query->cursor(),
                fn (Builder $query) => $query->get(),
            );
    }
}
