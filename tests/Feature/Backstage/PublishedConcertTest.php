<?php

use App\Models\Concert;
use App\Models\User;
use Illuminate\Http\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

test('a promoter can publish their own concerts', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->unpublished()->create(['ticket_quantity' => 3]);

	$response = actingAs($user)->post(uri: route('backstage.published-concerts.store'), data: [
		'concert_id' => $concert->id,
	]);

	$response
		->assertStatus(Response::HTTP_CREATED)
		->assertRedirect(route('backstage.concerts.index'));

	expect($concert->fresh())
		->isPublished()->toBeTrue()
		->ticketsRemaining()->toEqual(3);
});

test('a concert can only be published once', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->published(ticket_quantity: 3)->create();

	$response = actingAs($user)->post(uri: route('backstage.published-concerts.store'), data: [
		'concert_id' => $concert->id,
	]);

	$response
		->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

	expect($concert->fresh())
		->isPublished()->toBeTrue()
		->ticketsRemaining()->toEqual(3);
});

test('a promoter cannot publish another promoter\'s concerts', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->unpublished()->create();

	$response = actingAs($user)->post(uri: route('backstage.published-concerts.store'), data: [
		'concert_id' => $concert->id,
	]);

	$response
		->assertStatus(Response::HTTP_NOT_FOUND);

	expect($concert->fresh())
		->isPublished()->toBeFalse()
		->ticketsRemaining()->toEqual(0);
});

test('a promoter cannot publish concerts that do not exist', function () {
	$user = User::factory()->create();

	$response = actingAs($user)->post(uri: route('backstage.published-concerts.store'), data: [
		'concert_id' => 999,
	]);

	$response
		->assertStatus(Response::HTTP_NOT_FOUND);
});

test('guests cannot publish concerts', function () {
	$concert = Concert::factory()->unpublished()->create();

	$response = post(uri: route('backstage.published-concerts.store'), data: [
		'concert_id' => $concert->id,
	]);

	$response
		->assertStatus(Response::HTTP_FOUND)
		->assertRedirect(uri: route('auth.login'));

	expect($concert->fresh())
		->isPublished()->toBeFalse()
		->ticketsRemaining()->toEqual(0);
});