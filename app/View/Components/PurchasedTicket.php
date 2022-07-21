<?php

namespace App\View\Components;

use App\Models\Ticket;
use Illuminate\View\Component;

class PurchasedTicket extends Component
{
	public function __construct(public Ticket $ticket, public string $email) {}

	public function render()
	{
		return view('components.purchased-ticket');
	}
}
