<?php

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

test('the invitation code is part of the url to view an invitation', function () {
    $code = Str::uuid();
    $invitation = Invitation::factory()->create(['code' => $code]);

    $this->assertEquals(
        expected: route('invitations.show', [$invitation]),
        actual: url("/invitations/{$code}")
    );
});

test('viewing an unused invitation', function () {
    $invitation = Invitation::factory()->create();

    $response = get(uri: route('invitations.show', [$invitation]));

    $response
        ->assertStatus(Response::HTTP_OK)
        ->assertViewIs('invitations.show')
        ->assertViewHas('invitation', fn ($viewData) => $viewData->is($invitation));
});

test('viewing an used invitation', function () {
    $invitation = Invitation::factory()->for(User::factory())->create();

    $response = get(uri: route('invitations.show', [$invitation]));

    $response->assertStatus(Response::HTTP_NOT_FOUND);
});

test('viewing an invitation that does not exist')
    ->get("/invitations/SOMERANDOMCODE")
    ->assertStatus(Response::HTTP_NOT_FOUND);

test('registering with a valid invitation code', function () {
    $invitation = Invitation::factory()->unused()->create();
    expect(User::count())->toEqual(0);

    $response = post(uri: route('auth.register'), data: [
        'email' => 'john@example.com',
        'password' => 'asdf1234',
        'invitation_code' => $invitation->code,
    ]);

    $response
        ->assertStatus(Response::HTTP_CREATED)
        ->assertRedirect(uri: route('backstage.concerts.index'));

    $user = User::first();
    expect(User::count())->toEqual(1);
    expect($user)
        ->email->toEqual('john@example.com')
        ->and(Hash::check(value: 'asdf1234', hashedValue: $user->password))->toBeTrue()
        ->and($invitation->fresh())
            ->hasBeenUsed()->toBeTrue()
            ->user->is($user);
    $this->assertAuthenticatedAs(user: $user);
});

test('registering with an used invitation code', function () {
    $invitation = Invitation::factory()->used()->create();
    expect(User::count())->toEqual(1);

    $response = post(uri: route('auth.register'), data: [
        'email' => 'john@example.com',
        'password' => 'asdf1234',
        'invitation_code' => $invitation->code,
    ]);

    $response->assertStatus(Response::HTTP_NOT_FOUND);

    expect(User::count())->toEqual(1);
});

test('registering with an invitation code that doesn\'t exist', function () {
    expect(User::count())->toEqual(0);

    $response = post(uri: route('auth.register'), data: [
        'email' => 'john@example.com',
        'password' => 'asdf1234',
        'invitation_code' => 'RANDOMCODE',
    ]);

    $response->assertStatus(Response::HTTP_NOT_FOUND);

    expect(User::count())->toEqual(0);
});

test('email is required', function () {
    $invitation = Invitation::factory()->unused()->create();

    $response = from(route('invitations.show', [$invitation]))->post(uri: route('auth.register'), data: [
        'email' => '',
        'password' => 'asdf1234',
        'invitation_code' => $invitation->code,
    ]);

    $response
        ->assertStatus(Response::HTTP_FOUND)
        ->assertRedirect(uri: route('invitations.show', [$invitation]))
        ->assertSessionHasErrors('email');
});

test('email must be valid', function () {
    $invitation = Invitation::factory()->unused()->create();

    $response = from(route('invitations.show', [$invitation]))->post(uri: route('auth.register'), data: [
        'email' => 'not-an-email',
        'password' => 'asdf1234',
        'invitation_code' => $invitation->code,
    ]);

    $response
        ->assertStatus(Response::HTTP_FOUND)
        ->assertRedirect(uri: route('invitations.show', [$invitation]))
        ->assertSessionHasErrors('email');
});

test('email must be unique', function () {
    User::factory()->create(['email' => 'john@example.com']);
    $invitation = Invitation::factory()->unused()->create();

    $response = from(route('invitations.show', [$invitation]))->post(uri: route('auth.register'), data: [
        'email' => 'john@example.com',
        'password' => 'asdf1234',
        'invitation_code' => $invitation->code,
    ]);

    $response
        ->assertStatus(Response::HTTP_FOUND)
        ->assertRedirect(uri: route('invitations.show', [$invitation]))
        ->assertSessionHasErrors('email');
});

test('password is required', function () {
    $invitation = Invitation::factory()->unused()->create();

    $response = from(route('invitations.show', [$invitation]))->post(uri: route('auth.register'), data: [
        'email' => 'john@example.com',
        'password' => '',
        'invitation_code' => $invitation->code,
    ]);

    $response
        ->assertStatus(Response::HTTP_FOUND)
        ->assertRedirect(uri: route('invitations.show', [$invitation]))
        ->assertSessionHasErrors('password');
});

test('password must be at least 8 characters long', function () {
    $invitation = Invitation::factory()->unused()->create();

    $response = from(route('invitations.show', [$invitation]))->post(uri: route('auth.register'), data: [
        'email' => 'john@example.com',
        'password' => '1234567',
        'invitation_code' => $invitation->code,
    ]);

    $response
        ->assertStatus(Response::HTTP_FOUND)
        ->assertRedirect(uri: route('invitations.show', [$invitation]))
        ->assertSessionHasErrors('password');
});
