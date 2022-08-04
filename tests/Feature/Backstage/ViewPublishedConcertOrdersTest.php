<?php

use App\Models\Concert;
use App\Models\User;
use Illuminate\Http\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('a promoter can view the orders of their own published concert', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->published()->create();

	$response = actingAs(user: $user)->get(uri: route('backstage.published-concert-orders.index', [$concert]));

	$response
		->assertStatus(Response::HTTP_OK)
		->assertViewIs('backstage.published-concert-orders.index')
		->assertViewHas('concert', fn (Concert $viewConcert) => $viewConcert->is($concert));
});
