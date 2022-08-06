<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\LazyCollection;

class AttendeeMessage extends Model
{
    use HasFactory;

    protected $guarded = [];

    /* RELATIONSHIPS */
    public function concert(): BelongsTo
    {
        return $this->belongsTo(Concert::class);
    }

    /* METHODS */
    public function recipients(): LazyCollection
    {
        // some PostgreSQL magic
        return $this->concert
            ->orders() // get concert -> orders relationship
            ->getQuery() // get query
            ->distinct()->select('email') // override select statement instead of adding to it.
            ->lazyById(chunkSize: 20, column: 'email') // override ordering column so postgresql won't complain about it not being present in select
            ->pluck('email');
    }
}
