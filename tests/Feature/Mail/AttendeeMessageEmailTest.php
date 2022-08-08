<?php

use App\Mail\AttendeeMessageEmail;
use App\Models\AttendeeMessage;

it('has the correct subject and message', function () {
	$message = AttendeeMessage::factory()->make([
		'subject' => 'My subject',
		'message' => 'My message',
	]);
	$mailable = new AttendeeMessageEmail(attendeeMessage: $message);

	$mailable->build();

	$mailable->assertSeeInText('My message');
	expect($mailable)->hasSubject('My subject')->toBeTrue();
});
