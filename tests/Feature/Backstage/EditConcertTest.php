<?php

use App\Models\Concert;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Date;
use Illuminate\View\View;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;

function defaultUpdateData(array $overrides = []): array
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
			'ticket_quantity' => '10',
		],
		$overrides
	);
}

function defaultAttributes(array $overrides = []): array
{
	return array_merge(
		[
			'title' => 'Old title',
			'subtitle' => 'Old subtitle',
			'additional_information' => 'Old additional information',
			'date' => '2017-01-01 12:00:00',
			'venue' => 'Old venue',
			'venue_address' => 'Old venue address',
			'city' => 'Old city',
			'state' => 'Old state',
			'zip' => '00000',
			'ticket_price' => 2000,
			'ticket_quantity' => 5,
		],
		$overrides
	);
}

beforeEach(function () {
	$this->user = User::factory()->create();
});

test('promoters can view the edit form for their own unpublished concerts', function () {
	$concert = Concert::factory()->for($this->user)->create();
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs($this->user)->get(uri: route('backstage.concerts.edit', [$concert]));

	$response->assertStatus(Response::HTTP_OK);
	expect($response->original)
		->toBeInstanceOf(View::class)
		->getName()->toEqual('backstage.concerts.edit')
		->and($response->original->concert)
			->is($concert)->toBeTrue();
});

test('promoters cannot view the edit form for their own published concerts', function () {
	$concert = Concert::factory()->for($this->user)->published()->create();
	expect($concert)->isPublished()->toBeTrue();

	$response = actingAs($this->user)->get(uri: route('backstage.concerts.edit', [$concert]));

	$response->assertStatus(Response::HTTP_FORBIDDEN);
});

test('promoters cannot view the edit form for other concerts', function () {
	$otherUser = User::factory()->create();
	$concert = Concert::factory()->for($otherUser)->create();

	$response = actingAs($this->user)->get(uri: route('backstage.concerts.edit', [$concert]));

	$response->assertStatus(Response::HTTP_NOT_FOUND);
});

test('promoters see a 404 (Not Found) when trying to edit a concert that does not exist', function () {
	$response = actingAs($this->user)->get(uri: route('backstage.concerts.edit', [999]));

	$response->assertStatus(Response::HTTP_NOT_FOUND);
});

test('guests are asked to login when attempting to view the edit form for any concert', function () {
	$concert = Concert::factory()->create();

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
	$concert = Concert::factory()->for($this->user)->create(defaultAttributes());
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs($this->user)->patch(
		uri: route('backstage.concerts.update', [$concert]),
		data: defaultUpdateData(),
	);

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
		->ticket_price->toEqual(6750)
		->ticket_quantity->toEqual(10);
});

test('promoters cannot edit other unpublished concerts', function () {
	$otherUser = User::factory()->create();
	$concert = Concert::factory()->for($otherUser)->create(defaultAttributes());
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs($this->user)->patch(
		uri: route('backstage.concerts.update', [$concert]),
		data: defaultUpdateData(),
	);

	$response->assertStatus(Response::HTTP_NOT_FOUND);

	expect($concert->fresh())->getAttributes()->toMatchArray(defaultAttributes());
});

test('promoters cannot edit their own published concerts', function () {
	$concert = Concert::factory()->for($this->user)->published()->create(defaultAttributes());
	expect($concert)->isPublished()->toBeTrue();

	$response = actingAs($this->user)->patch(
		uri: route('backstage.concerts.update', [$concert]),
		data: defaultUpdateData(),
	);

	$response->assertStatus(Response::HTTP_FORBIDDEN);

	expect($concert->fresh())->getAttributes()->toMatchArray(defaultAttributes());
});

test('guests cannot edit concerts', function () {
	$concert = Concert::factory()->create(defaultAttributes());
	expect($concert)->isPublished()->toBeFalse();

	$response = patch(
		uri: route('backstage.concerts.update', [$concert]),
		data: defaultUpdateData(),
	);

	$response
		->assertStatus(Response::HTTP_FOUND)
		->assertRedirect(uri: route('auth.login'));

	expect($concert->fresh())->getAttributes()->toMatchArray(defaultAttributes());
});

test('title is required', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create(['title' => 'Old title']);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: defaultUpdateData(overrides: ['title' => ''])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['title']);

	expect($concert->fresh())->title->toEqual('Old title');
});

test('subtitle is optional', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create(['subtitle' => 'Old subtitle']);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: defaultUpdateData(overrides: ['subtitle' => ''])
		);

	$response
		->assertStatus(Response::HTTP_FOUND)
		->assertSessionHasNoErrors()
		->assertRedirect(uri: route('backstage.concerts.index'));

	expect($concert->fresh())->subtitle->toBeNull();
});

test('additional information is optional', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create(['additional_information' => 'Old additional information']);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: defaultUpdateData(overrides: ['additional_information' => ''])
		);

	$response
		->assertStatus(Response::HTTP_FOUND)
		->assertSessionHasNoErrors()
		->assertRedirect(uri: route('backstage.concerts.index'));

	expect($concert->fresh())->additional_information->toBeNull();
});

test('date is required', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create(['date' => Date::parse('2017-01-01 12:00:00')]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: defaultUpdateData(overrides: ['date' => ''])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['date']);

	expect($concert->fresh())->date->toEqual(Date::parse('2017-01-01 12:00:00'));
});

test('date must be valid', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create(['date' => Date::parse('2017-01-01 12:00:00')]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: defaultUpdateData(overrides: ['date' => 'not a date'])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['date']);

	expect($concert->fresh())->date->toEqual(Date::parse('2017-01-01 12:00:00'));
});

test('time is required', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create(['date' => Date::parse('2017-01-01 12:00:00')]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: defaultUpdateData(overrides: ['time' => ''])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['date']);

	expect($concert->fresh())->date->toEqual(Date::parse('2017-01-01 12:00:00'));
});

test('time must be valid', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create(['date' => Date::parse('2017-01-01 12:00:00')]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: defaultUpdateData(overrides: ['time' => 'not a time'])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['date']);

	expect($concert->fresh())->date->toEqual(Date::parse('2017-01-01 12:00:00'));
});

test('venue is required', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create(['venue' => 'Old venue']);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: defaultUpdateData(overrides: ['venue' => ''])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['venue']);

	expect($concert->fresh())->venue->toEqual('Old venue');
});

test('venue address is required', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create(['venue_address' => 'Old venue address']);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: defaultUpdateData(overrides: ['venue_address' => ''])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['venue_address']);

	expect($concert->fresh())->venue_address->toEqual('Old venue address');
});

test('city is required', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create(['city' => 'Old city']);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: defaultUpdateData(overrides: ['city' => ''])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['city']);

	expect($concert->fresh())->city->toEqual('Old city');
});

test('state is required', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create(['state' => 'Old state']);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: defaultUpdateData(overrides: ['state' => ''])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['state']);

	expect($concert->fresh())->state->toEqual('Old state');
});

test('zip is required', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create(['zip' => '00000']);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: defaultUpdateData(overrides: ['zip' => ''])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['zip']);

	expect($concert->fresh())->zip->toEqual('00000');
});

test('ticket price is required', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create(['ticket_price' => 2000]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: defaultUpdateData(overrides: ['ticket_price' => ''])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['ticket_price']);

	expect($concert->fresh())->ticket_price->toEqual(2000);
});

test('ticket price must be numeric', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create(['ticket_price' => 2000]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: defaultUpdateData(overrides: ['ticket_price' => 'not a number'])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['ticket_price']);

	expect($concert->fresh())->ticket_price->toEqual(2000);
});

test('ticket price must be at least 5', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create(['ticket_price' => 2000]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: defaultUpdateData(overrides: ['ticket_price' => '4.99'])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['ticket_price']);

	expect($concert->fresh())->ticket_price->toEqual(2000);
});

test('ticket quantity is required', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create(['ticket_quantity' => 5]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: defaultUpdateData(overrides: ['ticket_quantity' => ''])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['ticket_quantity']);

	expect($concert->fresh())->ticket_quantity->toEqual(5);
});

test('ticket quantity must be numeric', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create(['ticket_quantity' => 5]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: defaultUpdateData(overrides: ['ticket_quantity' => 'not a number'])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['ticket_quantity']);

	expect($concert->fresh())->ticket_quantity->toEqual(5);
});

test('ticket quantity must be an integer', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create(['ticket_quantity' => 5]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: defaultUpdateData(overrides: ['ticket_quantity' => '7.8'])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['ticket_quantity']);

	expect($concert->fresh())->ticket_quantity->toEqual(5);
});

test('ticket quantity must be at least 1', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->create(['ticket_quantity' => 5]);
	expect($concert)->isPublished()->toBeFalse();

	$response = actingAs(user: $user)
		->from(url: route('backstage.concerts.edit', [$concert]))
		->patch(
			uri: route('backstage.concerts.update', [$concert]),
			data: defaultUpdateData(overrides: ['ticket_quantity' => '0'])
		);

	$response
		->assertRedirect(uri: route('backstage.concerts.edit', [$concert]))
		->assertSessionHasErrors(keys: ['ticket_quantity']);

	expect($concert->fresh())->ticket_quantity->toEqual(5);
});
