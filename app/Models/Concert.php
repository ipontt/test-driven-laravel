<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /* METHODS */
    public function orderTickets(string $email, int $ticket_quantity): Order
    {
        $order = $this->orders()->create(['email' => $email]);

        for ($i = 0; $i < $ticket_quantity; $i++) { 
            $order->tickets()->create();
        }

        return $order;
    }
}
