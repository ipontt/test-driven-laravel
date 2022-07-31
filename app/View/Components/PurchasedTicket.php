<?php

namespace App\View\Components;

use App\Models\Ticket;
use Illuminate\View\Component;
use Illuminate\View\View;

use function view;

class PurchasedTicket extends Component
{
	public function __construct(public Ticket $ticket, public string $email) {}

	public function render(): View
	{
		return view('components.purchased-ticket');
	}
}
