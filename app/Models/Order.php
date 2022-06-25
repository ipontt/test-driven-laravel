<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

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
    public function cancel(): void
    {
        $this->tickets()->cursor()->each->release();

        $this->delete();
    }

    public function ticketQuantity(): int
    {
        return $this->tickets()->count();
    }
}
