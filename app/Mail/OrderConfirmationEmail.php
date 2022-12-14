<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationEmail extends Mailable
{
	use Queueable;
	use SerializesModels;

	public function __construct(public readonly Order $order) {}

	public function build(): self
	{
		return $this->subject('Your TicketBeast Order')->view('emails.order-confirmation-email');
	}
}
