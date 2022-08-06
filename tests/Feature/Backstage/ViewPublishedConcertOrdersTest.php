<?php

use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Date;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('a promoter can view the orders of their own published concert', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->published()->create();

	$response = actingAs(user: $user)->get(uri: route('backstage.published-concert-orders.index', [$concert]));

	$response
		->assertStatus(Response::HTTP_OK)
		->assertViewIs('backstage.published-concert-orders.index')
		->assertViewHas('concert', fn ($data) => $data->is($concert));
});

test('a promoter can view the 10 most recent orders for their published concert', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->published()->create();
	$orders = Order::factory()
		->has(Ticket::factory()->for($concert))
		->sequence(
			['created_at' => Date::parse('11 days ago')], // index 0
			['created_at' => Date::parse('10 days ago')], // index 1
			['created_at' => Date::parse('9 days ago')],  // index 2
			['created_at' => Date::parse('8 days ago')],  // index 3
			['created_at' => Date::parse('7 days ago')],  // index 4
			['created_at' => Date::parse('6 days ago')],  // index 5
			['created_at' => Date::parse('5 days ago')],  // index 6
			['created_at' => Date::parse('4 days ago')],  // index 7
			['created_at' => Date::parse('3 days ago')],  // index 8
			['created_at' => Date::parse('2 days ago')],  // index 9
			['created_at' => Date::parse('1 days ago')],  // index 10
		)
		->count(11)
		->create();

	$response = actingAs(user: $user)->get(uri: route('backstage.published-concert-orders.index', [$concert]));

	$response
		->assertStatus(Response::HTTP_OK)
		->assertViewIs('backstage.published-concert-orders.index')
		->assertViewHas('orders', function ($data) use ($orders) {
			expect($data)
				->toBeCollection()
				->toHaveCount(10)
				->first()->toBeSameModelAs($orders->get(10))
				->get(1)->toBeSameModelAs($orders->get(9))
				->get(2)->toBeSameModelAs($orders->get(8))
				->get(3)->toBeSameModelAs($orders->get(7))
				->get(4)->toBeSameModelAs($orders->get(6))
				->get(5)->toBeSameModelAs($orders->get(5))
				->get(6)->toBeSameModelAs($orders->get(4))
				->get(7)->toBeSameModelAs($orders->get(3))
				->get(8)->toBeSameModelAs($orders->get(2))
				->last()->toBeSameModelAs($orders->get(1));

			return true;
		});
});

test('the 10 most recent orders do not include duplicates', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->for($user)->published(ticket_quantity: 5)->create();
	$order = Order::factory()->create();
	$ticket = $concert->tickets->each(fn (Ticket $ticket) => $ticket->order()->associate($order)->save());

	$response = actingAs(user: $user)->get(uri: route('backstage.published-concert-orders.index', [$concert]));

	$response
		->assertStatus(Response::HTTP_OK)
		->assertViewIs('backstage.published-concert-orders.index')
		->assertViewHas('orders', function ($data) use ($order) {
			expect($data)
				->toBeCollection()
				->toHaveCount(1)
				->first()->toBeSameModelAs($order);

			return true;
		});
});

test('a promoter cannot view the orders of another promoter\'s published concert', function () {
	$user = User::factory()->create();
	$concert = Concert::factory()->published()->create();

	$response = actingAs(user: $user)->get(uri: route('backstage.published-concert-orders.index', [$concert]));

	$response->assertStatus(Response::HTTP_NOT_FOUND);
});

test('guests cannot view the orders of a published concert', function () {
	$concert = Concert::factory()->published()->create();

	$response = get(uri: route('backstage.published-concert-orders.index', [$concert]));

	$response
		->assertStatus(Response::HTTP_FOUND)
		->assertRedirect(uri: route('auth.login'));
});
