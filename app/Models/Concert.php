<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concert extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $dates = ['date'];

    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at');
    }

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
}
