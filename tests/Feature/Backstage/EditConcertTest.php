<?php

use App\Models\Concert;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Date;
use Illuminate\View\View;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;

function getValidNewConcertData(array $overrides = []): array
{
	return array_merge(
		[
			'title' => 'New title',
			'subtitle' => 'New subtitle',
			'additional_information' => 'New additional information',
			'date' => Date::parse('2022-01-01 18:30:00')->format('Y-m-d'),
			'time' => Date::parse('2022-01-01 18:30:00')->format('H:i'),
			'venue' => 'New venue',
			'venue_address' => 'New venue address',
			'city' => 'New city',
			'state' => 'New state',
			'zip' => '99999',
			'ticket_price' => '67.50',
		],
		$overrides
	);
}

test('promoters can view the edit form for their own unpublished concerts', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create();
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs($user)->get(uri: route('backstage.concerts.edit', [$concert]));

	$response->assertStatus(Response::HTTP_OK);
	expect($response->original)
		->toBeInstanceOf(View::class)
		->getName()->toEqual('backstage.concerts.edit')
		->and($response->original->concert)
			->is($concert)->toBeTrue();
});

test('promoters cannot view the edit form for their own published concerts', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->published()->create();
	expect($concert)->isPublished()->toBeTrue();

	$response = actingAs($user)->get(uri: route('backstage.concerts.edit', [$concert]));

	$response->assertStatus(Response::HTTP_FORBIDDEN);
});

test('promoters cannot view the edit form for other concerts', function () {
	[$user, $otherUser] = User::factory()->count(2)->create();
	$concert = Concert::factory()->for($otherUser)->create();

	$response = actingAs($user)->get(uri: route('backstage.concerts.edit', [$concert]));

	$response->assertStatus(Response::HTTP_NOT_FOUND);
});

test('promoters see a 404 (Not Found) when trying to edit a concert that does not exist', function () {
	$user = User::factory()->create();

	$response = actingAs($user)->get(uri: route('backstage.concerts.edit', [999]));

	$response->assertStatus(Response::HTTP_NOT_FOUND);
});

test('guests are asked to login when attempting to view the edit form for any concert', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create();

	$response = get(uri: route('backstage.concerts.edit', [$concert]));

	$response
		->assertStatus(Response::HTTP_FOUND)
		->assertRedirect(route('auth.login'));
});

test('guests are asked to login even if their are trying to edit a concert that does not exist', function () {
	$response = get(uri: route('backstage.concerts.edit', [999]));

	$response
		->assertStatus(Response::HTTP_FOUND)
		->assertRedirect(route('auth.login'));
});

test('promoters can edit their own unpublished concerts', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create([
		'title' => 'Old title',
		'subtitle' => 'Old subtitle',
		'additional_information' => 'Old additional information',
		'date' => Date::parse('2017-01-01 12:00:00'),
		'venue' => 'Old venue',
		'venue_address' => 'Old venue address',
		'city' => 'Old city',
		'state' => 'Old state',
		'zip' => '00000',
		'ticket_price' => 2000,
	]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs($user)->patch(uri: route('backstage.concerts.update', [$concert]), data: [
		'title' => 'New title',
		'subtitle' => 'New subtitle',
		'additional_information' => 'New additional information',
		'date' => Date::parse('2022-01-01 18:30:00')->format('Y-m-d'),
		'time' => Date::parse('2022-01-01 18:30:00')->format('H:i'),
		'venue' => 'New venue',
		'venue_address' => 'New venue address',
		'city' => 'New city',
		'state' => 'New state',
		'zip' => '99999',
		'ticket_price' => '67.50',
	]);

	$response
		->assertStatus(Response::HTTP_FOUND)
		->assertRedirect(uri: route('backstage.concerts.index'));

	expect($concert->fresh())
		->title->toEqual('New title')
		->subtitle->toEqual('New subtitle')
		->additional_information->toEqual('New additional information')
		->date->toEqual(Date::parse('2022-01-01 18:30:00'))
		->venue->toEqual('New venue')
		->venue_address->toEqual('New venue address')
		->city->toEqual('New city')
		->state->toEqual('New state')
		->zip->toEqual('99999')
		->ticket_price->toEqual(6750);
});

test('promoters cannot edit other unpublished concerts', function () {
	[$user, $otherUser] = User::factory()->count(2)->create();
	$concert = Concert::factory()->for($otherUser)->create([
		'title' => 'Old title',
		'subtitle' => 'Old subtitle',
		'additional_information' => 'Old additional information',
		'date' => Date::parse('2017-01-01 12:00:00'),
		'venue' => 'Old venue',
		'venue_address' => 'Old venue address',
		'city' => 'Old city',
		'state' => 'Old state',
		'zip' => '00000',
		'ticket_price' => 2000,
	]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs($user)->patch(uri: route('backstage.concerts.update', [$concert]), data: [
		'title' => 'New title',
		'subtitle' => 'New subtitle',
		'additional_information' => 'New additional information',
		'date' => Date::parse('2022-01-01 18:30:00')->format('Y-m-d'),
		'time' => Date::parse('2022-01-01 18:30:00')->format('H:i'),
		'venue' => 'New venue',
		'venue_address' => 'New venue address',
		'city' => 'New city',
		'state' => 'New state',
		'zip' => '99999',
		'ticket_price' => '67.50',
	]);

	$response->assertStatus(Response::HTTP_NOT_FOUND);

	expect($concert->fresh())
		->title->toEqual('Old title')
		->subtitle->toEqual('Old subtitle')
		->additional_information->toEqual('Old additional information')
		->date->toEqual(Date::parse('2017-01-01 12:00:00'))
		->venue->toEqual('Old venue')
		->venue_address->toEqual('Old venue address')
		->city->toEqual('Old city')
		->state->toEqual('Old state')
		->zip->toEqual('00000')
		->ticket_price->toEqual(2000);
});

test('promoters cannot edit their own published concerts', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->published()->create([
		'title' => 'Old title',
		'subtitle' => 'Old subtitle',
		'additional_information' => 'Old additional information',
		'date' => Date::parse('2017-01-01 12:00:00'),
		'venue' => 'Old venue',
		'venue_address' => 'Old venue address',
		'city' => 'Old city',
		'state' => 'Old state',
		'zip' => '00000',
		'ticket_price' => 2000,
	]);
	expect($concert)->isPublished()->toBeTrue();

	$response = actingAs($user)->patch(uri: route('backstage.concerts.update', [$concert]), data: [
		'title' => 'New title',
		'subtitle' => 'New subtitle',
		'additional_information' => 'New additional information',
		'date' => Date::parse('2022-01-01 18:30:00')->format('Y-m-d'),
		'time' => Date::parse('2022-01-01 18:30:00')->format('H:i'),
		'venue' => 'New venue',
		'venue_address' => 'New venue address',
		'city' => 'New city',
		'state' => 'New state',
		'zip' => '99999',
		'ticket_price' => '67.50',
	]);

	$response->assertStatus(Response::HTTP_FORBIDDEN);

	expect($concert->fresh())
		->title->toEqual('Old title')
		->subtitle->toEqual('Old subtitle')
		->additional_information->toEqual('Old additional information')
		->date->toEqual(Date::parse('2017-01-01 12:00:00'))
		->venue->toEqual('Old venue')
		->venue_address->toEqual('Old venue address')
		->city->toEqual('Old city')
		->state->toEqual('Old state')
		->zip->toEqual('00000')
		->ticket_price->toEqual(2000);
});

test('guests cannot edit concerts', function () {
	$concert = Concert::factory()->for(User::factory())->create([
		'title' => 'Old title',
		'subtitle' => 'Old subtitle',
		'additional_information' => 'Old additional information',
		'date' => Date::parse('2017-01-01 12:00:00'),
		'venue' => 'Old venue',
		'venue_address' => 'Old venue address',
		'city' => 'Old city',
		'state' => 'Old state',
		'zip' => '00000',
		'ticket_price' => 2000,
	]);
	expect($concert)->isPublished()->toBeFalse();

	$response = patch(uri: route('backstage.concerts.update', [$concert]), data: [
		'title' => 'New title',
		'subtitle' => 'New subtitle',
		'additional_information' => 'New additional information',
		'date' => Date::parse('2022-01-01 18:30:00')->format('Y-m-d'),
		'time' => Date::parse('2022-01-01 18:30:00')->format('H:i'),
		'venue' => 'New venue',
		'venue_address' => 'New venue address',
		'city' => 'New city',
		'state' => 'New state',
		'zip' => '99999',
		'ticket_price' => '67.50',
	]);

	$response
		->assertStatus(Response::HTTP_FOUND)
		->assertRedirect(uri: route('auth.login'));

	expect($concert->fresh())
		->title->toEqual('Old title')
		->subtitle->toEqual('Old subtitle')
		->additional_information->toEqual('Old additional information')
		->date->toEqual(Date::parse('2017-01-01 12:00:00'))
		->venue->toEqual('Old venue')
		->venue_address->toEqual('Old venue address')
		->city->toEqual('Old city')
		->state->toEqual('Old state')
		->zip->toEqual('00000')
		->ticket_price->toEqual(2000);
});

test('title is required', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create([
		'title' => 'Old title',
		'subtitle' => 'Old subtitle',
		'additional_information' => 'Old additional information',
		'date' => Date::parse('2017-01-01 12:00:00'),
		'venue' => 'Old venue',
		'venue_address' => 'Old venue address',
		'city' => 'Old city',
		'state' => 'Old state',
		'zip' => '00000',
		'ticket_price' => 2000,
	]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: getValidNewConcertData(overrides: ['title' => ''])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['title']);
});

test('subtitle is optional', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create([
		'title' => 'Old title',
		'subtitle' => 'Old subtitle',
		'additional_information' => 'Old additional information',
		'date' => Date::parse('2017-01-01 12:00:00'),
		'venue' => 'Old venue',
		'venue_address' => 'Old venue address',
		'city' => 'Old city',
		'state' => 'Old state',
		'zip' => '00000',
		'ticket_price' => 2000,
	]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: getValidNewConcertData(overrides: ['subtitle' => ''])
		);
	$concert = $concert->fresh();

	$response
		->assertStatus(Response::HTTP_FOUND)
		->assertSessionHasNoErrors()
		->assertRedirect(uri: route('backstage.concerts.index'));

	expect($concert)
		->subtitle->toBeNull()
		->and($concert->user->is($user))->toBeTrue();
});

test('additional information is optional', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create([
		'title' => 'Old title',
		'subtitle' => 'Old subtitle',
		'additional_information' => 'Old additional information',
		'date' => Date::parse('2017-01-01 12:00:00'),
		'venue' => 'Old venue',
		'venue_address' => 'Old venue address',
		'city' => 'Old city',
		'state' => 'Old state',
		'zip' => '00000',
		'ticket_price' => 2000,
	]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: getValidNewConcertData(overrides: ['additional_information' => ''])
		);
	$concert = $concert->fresh();

	$response
		->assertStatus(Response::HTTP_FOUND)
		->assertSessionHasNoErrors()
		->assertRedirect(uri: route('backstage.concerts.index'));

	expect($concert)
		->additional_information->toBeNull()
		->and($concert->user->is($user))->toBeTrue();
});

test('date is required', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create([
		'title' => 'Old title',
		'subtitle' => 'Old subtitle',
		'additional_information' => 'Old additional information',
		'date' => Date::parse('2017-01-01 12:00:00'),
		'venue' => 'Old venue',
		'venue_address' => 'Old venue address',
		'city' => 'Old city',
		'state' => 'Old state',
		'zip' => '00000',
		'ticket_price' => 2000,
	]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: getValidNewConcertData(overrides: ['date' => ''])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['date']);
});

test('date must be valid', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create([
		'title' => 'Old title',
		'subtitle' => 'Old subtitle',
		'additional_information' => 'Old additional information',
		'date' => Date::parse('2017-01-01 12:00:00'),
		'venue' => 'Old venue',
		'venue_address' => 'Old venue address',
		'city' => 'Old city',
		'state' => 'Old state',
		'zip' => '00000',
		'ticket_price' => 2000,
	]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: getValidNewConcertData(overrides: ['date' => 'not a date'])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['date']);
});

test('time is required', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create([
		'title' => 'Old title',
		'subtitle' => 'Old subtitle',
		'additional_information' => 'Old additional information',
		'date' => Date::parse('2017-01-01 12:00:00'),
		'venue' => 'Old venue',
		'venue_address' => 'Old venue address',
		'city' => 'Old city',
		'state' => 'Old state',
		'zip' => '00000',
		'ticket_price' => 2000,
	]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: getValidNewConcertData(overrides: ['time' => ''])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['date']);
});

test('time must be valid', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create([
		'title' => 'Old title',
		'subtitle' => 'Old subtitle',
		'additional_information' => 'Old additional information',
		'date' => Date::parse('2017-01-01 12:00:00'),
		'venue' => 'Old venue',
		'venue_address' => 'Old venue address',
		'city' => 'Old city',
		'state' => 'Old state',
		'zip' => '00000',
		'ticket_price' => 2000,
	]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(uri: route('backstage.concerts.update', [$concert]), data: [
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
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['date']);
});

test('venue is required', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create([
		'title' => 'Old title',
		'subtitle' => 'Old subtitle',
		'additional_information' => 'Old additional information',
		'date' => Date::parse('2017-01-01 12:00:00'),
		'venue' => 'Old venue',
		'venue_address' => 'Old venue address',
		'city' => 'Old city',
		'state' => 'Old state',
		'zip' => '00000',
		'ticket_price' => 2000,
	]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: getValidNewConcertData(overrides: ['venue' => ''])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['venue']);
});

test('venue address is required', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create([
		'title' => 'Old title',
		'subtitle' => 'Old subtitle',
		'additional_information' => 'Old additional information',
		'date' => Date::parse('2017-01-01 12:00:00'),
		'venue' => 'Old venue',
		'venue_address' => 'Old venue address',
		'city' => 'Old city',
		'state' => 'Old state',
		'zip' => '00000',
		'ticket_price' => 2000,
	]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: getValidNewConcertData(overrides: ['venue_address' => ''])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['venue_address']);
});

test('city is required', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create([
		'title' => 'Old title',
		'subtitle' => 'Old subtitle',
		'additional_information' => 'Old additional information',
		'date' => Date::parse('2017-01-01 12:00:00'),
		'venue' => 'Old venue',
		'venue_address' => 'Old venue address',
		'city' => 'Old city',
		'state' => 'Old state',
		'zip' => '00000',
		'ticket_price' => 2000,
	]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: getValidNewConcertData(overrides: ['city' => ''])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['city']);
});

test('state is required', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create([
		'title' => 'Old title',
		'subtitle' => 'Old subtitle',
		'additional_information' => 'Old additional information',
		'date' => Date::parse('2017-01-01 12:00:00'),
		'venue' => 'Old venue',
		'venue_address' => 'Old venue address',
		'city' => 'Old city',
		'state' => 'Old state',
		'zip' => '00000',
		'ticket_price' => 2000,
	]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: getValidNewConcertData(overrides: ['state' => ''])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['state']);
});

test('zip is required', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create([
		'title' => 'Old title',
		'subtitle' => 'Old subtitle',
		'additional_information' => 'Old additional information',
		'date' => Date::parse('2017-01-01 12:00:00'),
		'venue' => 'Old venue',
		'venue_address' => 'Old venue address',
		'city' => 'Old city',
		'state' => 'Old state',
		'zip' => '00000',
		'ticket_price' => 2000,
	]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: getValidNewConcertData(overrides: ['zip' => ''])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['zip']);
});

test('ticket price is required', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create([
		'title' => 'Old title',
		'subtitle' => 'Old subtitle',
		'additional_information' => 'Old additional information',
		'date' => Date::parse('2017-01-01 12:00:00'),
		'venue' => 'Old venue',
		'venue_address' => 'Old venue address',
		'city' => 'Old city',
		'state' => 'Old state',
		'zip' => '00000',
		'ticket_price' => 2000,
	]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: getValidNewConcertData(overrides: ['ticket_price' => ''])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['ticket_price']);
});

test('ticket price must be numeric', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create([
		'title' => 'Old title',
		'subtitle' => 'Old subtitle',
		'additional_information' => 'Old additional information',
		'date' => Date::parse('2017-01-01 12:00:00'),
		'venue' => 'Old venue',
		'venue_address' => 'Old venue address',
		'city' => 'Old city',
		'state' => 'Old state',
		'zip' => '00000',
		'ticket_price' => 2000,
	]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: getValidNewConcertData(overrides: ['ticket_price' => 'not a number'])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['ticket_price']);
});

test('ticket price must be at least 5', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create([
		'title' => 'Old title',
		'subtitle' => 'Old subtitle',
		'additional_information' => 'Old additional information',
		'date' => Date::parse('2017-01-01 12:00:00'),
		'venue' => 'Old venue',
		'venue_address' => 'Old venue address',
		'city' => 'Old city',
		'state' => 'Old state',
		'zip' => '00000',
		'ticket_price' => 2000,
	]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: getValidNewConcertData(overrides: ['ticket_price' => '4.99'])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['ticket_price']);
});