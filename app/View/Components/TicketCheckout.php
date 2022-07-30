<?php

namespace App\View\Components;

use App\Models\Concert;
use Illuminate\View\Component;
use Illuminate\View\View;

class TicketCheckout extends Component
{
	public function __construct(public Concert $concert) {}

	public function render(): View
	{
		return \view('components.ticket-checkout');
	}
}
