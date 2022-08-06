<?php

namespace App\Jobs;

use App\Mail\AttendeeMessageEmail;
use App\Models\AttendeeMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAttendeeMessage implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public function __construct(public readonly AttendeeMessage $attendeeMessage) {}

	public function handle()
	{
		$this->attendeeMessage
			->recipients()
			->each(fn (string $recipient) => Mail::to($recipient)->queue(
				(new AttendeeMessageEmail($this->attendeeMessage))->onQueue(queue: 'emails'),
			));
	}
}
