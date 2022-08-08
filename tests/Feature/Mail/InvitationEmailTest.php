<?php

use App\Mail\InvitationEmail;
use App\Models\Invitation;

beforeEach(function () {
	$this->invitation = Invitation::factory()->create();
	$this->mailable = new InvitationEmail(invitation: $this->invitation);
});

it('contains a link to the invitation confirmation page', function () {
	$this->mailable->assertSeeInHtml(route('invitations.show', [$this->invitation]));
});

it('has a subject', function () {
	$this->mailable->build();
	
	expect($this->mailable)->hasSubject('Your TicketBeast invitation')->toBeTrue();
});
