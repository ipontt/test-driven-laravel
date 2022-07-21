<?php

namespace App\Http\Resources;

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
			'confirmation_number' => $this->confirmation_number,
			'email' => $this->email,
			'ticket_quantity' => $this->ticketQuantity(),
			'amount' => $this->amount,
		];
	}
}
