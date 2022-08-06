<?php

use App\Jobs\SendAttendeeMessage;
use App\Mail\AttendeeMessageEmail;
use App\Models\AttendeeMessage;
use App\Models\Concert;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

it('sends the message to all concert attendees', function () {
	Mail::fake();
	$concert = Concert::factory()->published(ticket_quantity: 5)->create();
	$message = AttendeeMessage::factory()->for($concert)->create([
		'subject' => 'My subject',
		'message' => 'My message',
	]);
	$orders = Order::factory()
		->sequence(
			['email' => 'alice@example.com'],
			['email' => 'alice@example.com'],
			['email' => 'bob@example.com'],
			['email' => 'bob@example.com'],
			['email' => 'carol@example.com'],
		)
		->count(5)
		->create()
		->each(fn (Order $order, $index) => $concert->tickets->get($index)->claimFor(order: $order));

	SendAttendeeMessage::dispatchSync(attendeeMessage: $message);

	Mail::assertQueued(
		mailable: AttendeeMessageEmail::class,
		callback: fn (AttendeeMessageEmail $mail) => $mail->hasTo('alice@example.com') && $mail->attendeeMessage->is($message)
	);
	Mail::assertQueued(
		mailable: AttendeeMessageEmail::class,
		callback: fn (AttendeeMessageEmail $mail) => $mail->hasTo('bob@example.com') && $mail->attendeeMessage->is($message)
	);
	Mail::assertQueued(
		mailable: AttendeeMessageEmail::class,
		callback: fn (AttendeeMessageEmail $mail) => $mail->hasTo('carol@example.com') && $mail->attendeeMessage->is($message)
	);
	Mail::assertQueued(mailable: AttendeeMessageEmail::class, callback: 3);
});
