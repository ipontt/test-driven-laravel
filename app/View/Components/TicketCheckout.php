<?php

namespace App\View\Components;

use App\Models\Concert;
use Illuminate\View\Component;

class TicketCheckout extends Component
{
    public function __construct(public Concert $concert) {}

    public function render()
    {
        return view('components.ticket-checkout');
    }
}
