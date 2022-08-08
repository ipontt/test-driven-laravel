<?php

use App\Mail\InvitationEmail;
use App\Models\Invitation;
use Illuminate\Support\Facades\Mail;

test('inviting a promoter via the cli', function () {
    Mail::fake();
    expect(Invitation::count())->toEqual(0);

    $this->artisan(command: 'invite-promoter', parameters: ['email' => 'john@example.com']);

    $invitation = Invitation::first();
    expect(Invitation::count())->toEqual(1);
    expect($invitation)
        ->user_id->toBeNull()
        ->code->not->toBeNull()
        ->email->toEqual('john@example.com');

    Mail::assertSent(InvitationEmail::class, function (InvitationEmail $mail) use ($invitation) {
        return $mail->hasTo('john@example.com')
            && $mail->invitation->is($invitation);
    });
});
