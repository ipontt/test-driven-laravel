<?php

use App\Jobs\SendAttendeeMessage;
use App\Models\AttendeeMessage;
use App\Models\Concert;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

test('a promoter can view the message form for their own concert', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create();

	$response = actingAs(user: $user)->get(uri: route('backstage.concert-messages.create', [$concert]));

	$response
		->assertStatus(Response::HTTP_OK)
		->assertViewIs('backstage.concert-messages.create')
		->assertViewHas('concert', fn ($data) => $data->is($concert));
});

test('a promoter cannot view the message form for another promoter\'s concert', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->create();

	$response = actingAs(user: $user)->get(uri: route('backstage.concert-messages.create', [$concert]));

	$response->assertStatus(Response::HTTP_NOT_FOUND);
});

test('a guest cannot view the message form for any concert', function () {
	$concert = Concert::factory()->create();

	$response = get(uri: route('backstage.concert-messages.create', [$concert]));

	$response
		->assertStatus(Response::HTTP_FOUND)
		->assertRedirect(uri: route('auth.login'));
});

test('a promoter can send a new message to the attendees of their own concerts', function () {
	Queue::fake();
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create();
	Queue::assertNothingPushed();

	$response = actingAs(user: $user)->post(uri: route('backstage.concert-messages.store', [$concert]), data: [
		'subject' => 'My subject',
		'message' => 'My message',
	]);
	$message = AttendeeMessage::first();

	$response
		->assertStatus(Response::HTTP_CREATED)
		->assertRedirect(uri: route('backstage.concert-messages.create', [$concert]))
		->assertSessionHas('flash');

	expect($message)
		->concert_id->toEqual($concert->id)
		->subject->toEqual('My subject')
		->message->toEqual('My message');

	Queue::assertPushed(
		job: SendAttendeeMessage::class,
		callback: fn (SendAttendeeMessage $job): bool => $job->attendeeMessage->is($message),
	);
});

test('a promoter cannot send a new message to the attendees of other promoter\'s concerts', function () {
	Queue::fake();
	$user = User::factory()->create();
	$concert = Concert::factory()->create();

	$response = actingAs(user: $user)->post(uri: route('backstage.concert-messages.store', [$concert]), data: [
		'subject' => 'My subject',
		'message' => 'My message',
	]);
	$message = AttendeeMessage::first();

	$response->assertStatus(Response::HTTP_NOT_FOUND);

	expect($message)->toBeNull();
	Queue::assertNotPushed(SendAttendeeMessage::class);
});

test('guests cannot send a new message to any concert', function () {
	Queue::fake();
	$concert = Concert::factory()->create();

	$response = post(uri: route('backstage.concert-messages.store', [$concert]), data: [
		'subject' => 'My subject',
		'message' => 'My message',
	]);
	$message = AttendeeMessage::first();

	$response
		->assertStatus(Response::HTTP_FOUND)
		->assertRedirect(uri: route('auth.login'));

	expect($message)->toBeNull();
	Queue::assertNotPushed(SendAttendeeMessage::class);
});

test('subject is required', function () {
	Queue::fake();
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concert-messages.create', [$concert]))
		->post(uri: route('backstage.concert-messages.store', [$concert]), data: [
			'subject' => '',
			'message' => 'My message',
		]);

	$response
		->assertRedirect(uri: route('backstage.concert-messages.create', [$concert]))
		->assertSessionHasErrors(keys: ['subject']);
	Queue::assertNotPushed(SendAttendeeMessage::class);
});

test('message is required', function () {
	Queue::fake();
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concert-messages.create', [$concert]))
		->post(uri: route('backstage.concert-messages.store', [$concert]), data: [
			'subject' => 'My subject',
			'message' => '',
		]);

	$response
		->assertRedirect(uri: route('backstage.concert-messages.create', [$concert]))
		->assertSessionHasErrors(keys: ['message']);
	Queue::assertNotPushed(SendAttendeeMessage::class);
});
