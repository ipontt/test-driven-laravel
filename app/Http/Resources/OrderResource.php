<?php

namespace App\Http\Resources;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arrayable;
use JsonSerializable;

class OrderResource extends JsonResource
{
	/* Transform the resource into an array. */
	public function toArray($request): Arrayable|JsonSerializable|array
	{
		return [
			'amount' => $this->amount,
			'confirmation_number' => $this->confirmation_number,
			'email' => $this->email,
			'tickets' => $this->tickets->map(fn (Ticket $ticket) => ['code' => $ticket->code])->all(),
		];
	}
}
