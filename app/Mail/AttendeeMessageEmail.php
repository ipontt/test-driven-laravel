<?php

namespace App\Mail;

use App\Models\AttendeeMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AttendeeMessageEmail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly AttendeeMessage $attendeeMessage) {}

    public function build(): self
    {
        return $this->subject($this->attendeeMessage->subject)->text('emails.attendee-message-email');
    }
}
