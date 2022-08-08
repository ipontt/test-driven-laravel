<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvitationEmail extends Mailable
{
	use Queueable;
	use SerializesModels;

	public function __construct(public readonly Invitation $invitation) {}

	public function build(): self
	{
		return $this->subject('Your TicketBeast invitation')->view('emails.invitation-email');
	}
}
