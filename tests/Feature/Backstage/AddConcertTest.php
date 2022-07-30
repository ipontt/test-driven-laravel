<?php

use App\Models\Concert;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Date;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

function getValidConcertData(array $overrides = []): array
{
	return \array_merge(
		[
			'title' => 'The Red Cord',
			'subtitle' => 'with Animosity and Lethargy',
			'additional_information' => 'You must be 19 years of age to attend this concert.',
			'date' => '2022-07-06',
			'time' => '20:00',
			'venue' => 'The Mosh Pit',
			'venue_address' => '123 Example Lane',
			'city' => 'Laraville',
			'state' => 'ON',
			'zip' => '17916',
			'ticket_price' => '32.50',
			'ticket_quantity' => '75',
		],
		$overrides
	);
}

test('promoters can view the Add Concert form', function () {
	$user = User::factory()->create();

	$response = actingAs(user: $user)->get(uri: \route('backstage.concerts.create'));

	$response->assertStatus(Response::HTTP_OK);
});

test('guests cannot view the Add Concert form', function () {
	$response = get(\route('backstage.concerts.create'));

	$response
		->assertStatus(Response::HTTP_FOUND)
		->assertRedirect(uri: \route('auth.show-login'));
});

test('adding a valid concert', function () {
	$user = User::factory()->create();

	$response = actingAs(user: $user)->post(uri: \route('backstage.concerts.store'), data: [
		'title' => 'The Red Cord',
		'subtitle' => 'with Animosity and Lethargy',
		'additional_information' => 'You must be 19 years of age to attend this concert.',
		'date' => '2022-07-06',
		'time' => '20:00',
		'venue' => 'The Mosh Pit',
		'venue_address' => '123 Example Lane',
		'city' => 'Laraville',
		'state' => 'ON',
		'zip' => '17916',
		'ticket_price' => '32.50',
		'ticket_quantity' => '75',
	]);
	$concert = Concert::first();

	$response
		->assertStatus(Response::HTTP_FOUND)
		->assertSessionHasNoErrors()
		->assertRedirect(uri: \route('concerts.show', [$concert]));

	expect($concert)
		->title->toEqual('The Red Cord')
		->subtitle->toEqual('with Animosity and Lethargy')
		->additional_information->toEqual('You must be 19 years of age to attend this concert.')
		->date->toEqual(Date::parse('2022-07-06 20:00:00'))
		->venue->toEqual('The Mosh Pit')
		->venue_address->toEqual('123 Example Lane')
		->city->toEqual('Laraville')
		->state->toEqual('ON')
		->zip->toEqual('17916')
		->ticket_price->toEqual(3250)
		->ticketsRemaining()->toEqual(75)
		->isPublished()->toBeTrue()
		->and($concert->user->is($user))->toBeTrue();
});

test('guests cannot add new concerts', function () {
	$response = post(uri: \route('backstage.concerts.store'), data: getValidConcertData());

	$response
		->assertStatus(Response::HTTP_FOUND)
		->assertRedirect(uri: \route('auth.show-login'));

	expect(Concert::count())->toEqual(0);
});

test('title is required', function () {
	$user = User::factory()->create();

	$response = actingAs(user: $user)
		->from(url: \route('backstage.concerts.create'))
		->post(
			uri: \route('backstage.concerts.store'),
			data: getValidConcertData(overrides: ['title' => ''])
		);

	$response
		->assertRedirect(uri: \route('backstage.concerts.create'))
		->assertSessionHasErrors(keys: ['title']);
});

test('subtitle is optional', function () {
	$user = User::factory()->create();

	$response = actingAs(user: $user)
		->from(url: \route('backstage.concerts.create'))
		->post(
			uri: \route('backstage.concerts.store'),
			data: getValidConcertData(overrides: ['subtitle' => ''])
		);
	$concert = Concert::first();

	$response
		->assertStatus(Response::HTTP_FOUND)
		->assertSessionHasNoErrors()
		->assertRedirect(uri: \route('concerts.show', [$concert]));

	expect($concert)
		->subtitle->toBeNull()
		->and($concert->user->is($user))->toBeTrue();
});

test('additional information is optional', function () {
	$user = User::factory()->create();

	$response = actingAs(user: $user)
		->from(url: \route('backstage.concerts.create'))
		->post(
			uri: \route('backstage.concerts.store'),
			data: getValidConcertData(overrides: ['additional_information' => ''])
		);
	$concert = Concert::first();

	$response
		->assertStatus(Response::HTTP_FOUND)
		->assertSessionHasNoErrors()
		->assertRedirect(uri: \route('concerts.show', [$concert]));

	expect($concert)
		->additional_information->toBeNull()
		->and($concert->user->is($user))->toBeTrue();
});

test('date is required', function () {
	$user = User::factory()->create();

	$response = actingAs(user: $user)
		->from(url: \route('backstage.concerts.create'))
		->post(
			uri: \route('backstage.concerts.store'),
			data: getValidConcertData(overrides: ['date' => ''])
		);

	$response
		->assertRedirect(uri: \route('backstage.concerts.create'))
		->assertSessionHasErrors(keys: ['date']);
});

test('date must be valid', function () {
	$user = User::factory()->create();

	$response = actingAs(user: $user)
		->from(url: \route('backstage.concerts.create'))
		->post(
			uri: \route('backstage.concerts.store'),
			data: getValidConcertData(overrides: ['date' => 'not a date'])
		);

	$response
		->assertRedirect(uri: \route('backstage.concerts.create'))
		->assertSessionHasErrors(keys: ['date']);
});

test('time is required', function () {
	$user = User::factory()->create();

	$response = actingAs(user: $user)
		->from(url: \route('backstage.concerts.create'))
		->post(
			uri: \route('backstage.concerts.store'),
			data: getValidConcertData(overrides: ['time' => ''])
		);

	$response
		->assertRedirect(uri: \route('backstage.concerts.create'))
		->assertSessionHasErrors(keys: ['date']);
});

test('time must be valid', function () {
	$user = User::factory()->create();

	$response = actingAs(user: $user)
		->from(url: \route('backstage.concerts.create'))
		->post(uri: \route('backstage.concerts.store'), data: [
			'title' => 'The Red Cord',
			'subtitle' => 'with Animosity and Lethargy',
			'additional_information' => 'You must be 19 years of age to attend this concert.',
			'time' => 'not a time',
			'venue' => 'The Mosh Pit',
			'venue_address' => '123 Example Lane',
			'city' => 'Laraville',
			'state' => 'ON',
			'zip' => '17916',
			'ticket_price' => '32.50',
			'ticket_quantity' => '75'
		]);

	$response
		->assertRedirect(uri: \route('backstage.concerts.create'))
		->assertSessionHasErrors(keys: ['date']);
});

test('venue is required', function () {
	$user = User::factory()->create();

	$response = actingAs(user: $user)
		->from(url: \route('backstage.concerts.create'))
		->post(
			uri: \route('backstage.concerts.store'),
			data: getValidConcertData(overrides: ['venue' => ''])
		);

	$response
		->assertRedirect(uri: \route('backstage.concerts.create'))
		->assertSessionHasErrors(keys: ['venue']);
});

test('venue address is required', function () {
	$user = User::factory()->create();

	$response = actingAs(user: $user)
		->from(url: \route('backstage.concerts.create'))
		->post(
			uri: \route('backstage.concerts.store'),
			data: getValidConcertData(overrides: ['venue_address' => ''])
		);

	$response
		->assertRedirect(uri: \route('backstage.concerts.create'))
		->assertSessionHasErrors(keys: ['venue_address']);
});

test('city is required', function () {
	$user = User::factory()->create();

	$response = actingAs(user: $user)
		->from(url: \route('backstage.concerts.create'))
		->post(
			uri: \route('backstage.concerts.store'),
			data: getValidConcertData(overrides: ['city' => ''])
		);

	$response
		->assertRedirect(uri: \route('backstage.concerts.create'))
		->assertSessionHasErrors(keys: ['city']);
});

test('state is required', function () {
	$user = User::factory()->create();

	$response = actingAs(user: $user)
		->from(url: \route('backstage.concerts.create'))
		->post(
			uri: \route('backstage.concerts.store'),
			data: getValidConcertData(overrides: ['state' => ''])
		);

	$response
		->assertRedirect(uri: \route('backstage.concerts.create'))
		->assertSessionHasErrors(keys: ['state']);
});

test('zip is required', function () {
	$user = User::factory()->create();

	$response = actingAs(user: $user)
		->from(url: \route('backstage.concerts.create'))
		->post(
			uri: \route('backstage.concerts.store'),
			data: getValidConcertData(overrides: ['zip' => ''])
		);

	$response
		->assertRedirect(uri: \route('backstage.concerts.create'))
		->assertSessionHasErrors(keys: ['zip']);
});

test('ticket price is required', function () {
	$user = User::factory()->create();

	$response = actingAs(user: $user)
		->from(url: \route('backstage.concerts.create'))
		->post(
			uri: \route('backstage.concerts.store'),
			data: getValidConcertData(overrides: ['ticket_price' => ''])
		);

	$response
		->assertRedirect(uri: \route('backstage.concerts.create'))
		->assertSessionHasErrors(keys: ['ticket_price']);
});

test('ticket price must be numeric', function () {
	$user = User::factory()->create();

	$response = actingAs(user: $user)
		->from(url: \route('backstage.concerts.create'))
		->post(
			uri: \route('backstage.concerts.store'),
			data: getValidConcertData(overrides: ['ticket_price' => 'not a number'])
		);

	$response
		->assertRedirect(uri: \route('backstage.concerts.create'))
		->assertSessionHasErrors(keys: ['ticket_price']);
});

test('ticket price must be at least 5', function () {
	$user = User::factory()->create();

	$response = actingAs(user: $user)
		->from(url: \route('backstage.concerts.create'))
		->post(
			uri: \route('backstage.concerts.store'),
			data: getValidConcertData(overrides: ['ticket_price' => '4.99'])
		);

	$response
		->assertRedirect(uri: \route('backstage.concerts.create'))
		->assertSessionHasErrors(keys: ['ticket_price']);
});

test('ticket quantity is required', function () {
	$user = User::factory()->create();

	$response = actingAs(user: $user)
		->from(url: \route('backstage.concerts.create'))
		->post(
			uri: \route('backstage.concerts.store'),
			data: getValidConcertData(overrides: ['ticket_quantity' => ''])
		);

	$response
		->assertRedirect(uri: \route('backstage.concerts.create'))
		->assertSessionHasErrors(keys: ['ticket_quantity']);
});

test('ticket quantity must be numeric', function () {
	$user = User::factory()->create();

	$response = actingAs(user: $user)
		->from(url: \route('backstage.concerts.create'))
		->post(
			uri: \route('backstage.concerts.store'),
			data: getValidConcertData(overrides: ['ticket_quantity' => 'not a number'])
		);

	$response
		->assertRedirect(uri: \route('backstage.concerts.create'))
		->assertSessionHasErrors(keys: ['ticket_quantity']);
});

test('ticket quantity must be at least 1', function () {
	$user = User::factory()->create();

	$response = actingAs(user: $user)
		->from(url: \route('backstage.concerts.create'))
		->post(
			uri: \route('backstage.concerts.store'),
			data: getValidConcertData(overrides: ['ticket_quantity' => '0'])
		);

	$response
		->assertRedirect(uri: \route('backstage.concerts.create'))
		->assertSessionHasErrors(keys: ['ticket_quantity']);
});
