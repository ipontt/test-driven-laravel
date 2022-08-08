<?php

namespace App\Models;

use App\Mail\InvitationEmail;
use App\Models\Traits\ResolvesUUIDFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Mail;

class Invitation extends Model
{
	use HasFactory;
	use ResolvesUUIDFields;

	protected $guarded = [];

	/* RELATIONSHIPS */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	/* METHODS */
	public function hasBeenUsed(): bool
	{
		return null !== $this->user_id;
	}

	public function send(): void
	{
		Mail::to($this->email)->send(new InvitationEmail(invitation: $this));
	}
}
