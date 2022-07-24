<?php

namespace App;

use App\Models\Ticket;
use Hashids\Hashids;

class HashidsTicketCodeGenerator implements TicketCodeGenerator
{
	private Hashids $hashids;

	public function __construct(?string $salt = null)
	{
		$this->hashids = new Hashids(
			salt: $salt ?? config('app.key'),
			minHashLength: 6,
			alphabet: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
		);
	}

	public function generateFor(Ticket $ticket): string
	{
		return $this->hashids->encode($ticket->id);
	}
}