<?php

use App\Models\Concert;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Http\Response;
use Illuminate\View\View;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('guests cannot view a promoter\'s concert list', function () {
	$response = get(url('/backstage/concerts'));

	$response
		->assertStatus(Response::HTTP_FOUND)
		->assertRedirect(uri: route('auth.show-login'));
});

test('promoters can only view a list of their own concerts', function () {
	[$user, $otherUser] = User::factory()->count(2)->create();
	$published_concerts = Concert::factory()
		->count(3)
		->published()
		->sequence(
			['user_id' => $user->id],
			['user_id' => $otherUser->id],
			['user_id' => $user->id],
		)
		->create();
	$unpublished_concerts = Concert::factory()
		->count(3)
		->unpublished()
		->sequence(
			['user_id' => $user->id],
			['user_id' => $otherUser->id],
			['user_id' => $user->id],
		)
		->create();

	$response = actingAs($user)->get(url('/backstage/concerts'));

	$response->assertStatus(Response::HTTP_OK);
	expect($response->original)
		->toBeInstanceOf(View::class)
		->getName()->toEqual('backstage.concerts.index')
		->and($response->original->published_concerts)
			->contains($published_concerts->get(0))->toBeTrue()
			->contains($published_concerts->get(1))->not->toBeTrue()
			->contains($published_concerts->get(2))->toBeTrue()
			->contains($unpublished_concerts->get(0))->not->toBeTrue()
			->contains($unpublished_concerts->get(1))->not->toBeTrue()
			->contains($unpublished_concerts->get(2))->not->toBeTrue()
		->and($response->original->unpublished_concerts)
			->contains($published_concerts->get(0))->not->toBeTrue()
			->contains($published_concerts->get(1))->not->toBeTrue()
			->contains($published_concerts->get(2))->not->toBeTrue()
			->contains($unpublished_concerts->get(0))->toBeTrue()
			->contains($unpublished_concerts->get(1))->not->toBeTrue()
			->contains($unpublished_concerts->get(2))->toBeTrue();
});
