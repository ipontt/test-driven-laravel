<?php

use App\Billing\Charge;
use App\Http\Resources\OrderResource;
use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use App\Reservation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\LazyCollection;

it('can be created from tickets, email and a charge', function () {
	$mockTickets = LazyCollection::make([
		Mockery::spy(Ticket::class),
		Mockery::spy(Ticket::class),
		Mockery::spy(Ticket::class),
	]);

	$order = Order::forTickets(
		tickets: $mockTickets,
		email: 'john@example.com',
		charge: new Charge(amount: 3000, cardLastFour: '1234')
	);

	expect($order)
		->amount->toEqual(3000)
		->card_last_four->toEqual('1234')
		->email->toEqual('john@example.com');

	$mockTickets->each->shouldHaveReceived(method: 'claimFor', args: [$order]);
});

it('can get the amount in dollars')
	->expect(fn () => Order::factory()->make(['amount' => 6750]))
	->amount_in_dollars->toBe('67.50');

it('can get the masked card number')
	->expect(fn () => Order::factory()->make(['card_last_four' => '4242']))
	->maked_card_number->toBe('**** **** **** 4242');

it('can be retrieved by its confirmation number', function () {
	$confirmation_number = Str::uuid();
	$order = Order::factory()->create(['confirmation_number' => $confirmation_number]);

	expect(Order::findByConfirmationNumber($confirmation_number))
		->toBeInstanceOf(Order::class)
		->id->toEqual($order->id);
});

it('throws an exception when retrieving a non-existent order by its confirmation number')
	->tap(fn () => Order::findByConfirmationNumber('00000000-0000-0000-0000-000000000000'))
	->throws(ModelNotFoundException::class);

it('can be json serialized', function () {
	$confirmation_number = Str::uuid();
	$concert = Concert::factory()->create();
	$order = Order::factory()
		->has(
			Ticket::factory()
				->count(3)
				->state(new Sequence(
					['code' => 'TICKETCODE1', 'concert_id' => $concert->id],
					['code' => 'TICKETCODE2', 'concert_id' => $concert->id],
					['code' => 'TICKETCODE3', 'concert_id' => $concert->id],
				))
		)
		->create([
			'confirmation_number' => $confirmation_number,
			'email' => 'jane@example.com',
			'amount' => 5000,
		]);

	$result = OrderResource::make($order)->jsonSerialize();

	expect($result)->toMatchArray([
		'amount' => 5000,
		'confirmation_number' => $confirmation_number,
		'email' => 'jane@example.com',
		'tickets' => [
			['code' => 'TICKETCODE1'],
			['code' => 'TICKETCODE2'],
			['code' => 'TICKETCODE3'],
		],
	]);
});
