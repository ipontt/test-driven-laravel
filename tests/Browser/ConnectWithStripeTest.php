<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\Config;
use Stripe\Account;

afterEach(function () {
	$this->browse(fn (Browser $browser) => $browser->logout());
});

test('connecting a stripe account successfully', function () {
	$user = User::factory()->create([
		'stripe_account_id' => null,
		'stripe_access_token' => null,
	]);

	$this->browse(function (Browser $browser) use ($user) {
		$browser->loginAs(userId: $user)
			->visitRoute(route: 'backstage.stripe-connect.connect')
			->clickLink('Connect with Stripe')
			->assertUrlIs(url: 'https://connect.stripe.com/oauth/v2/authorize')
			->assertQueryStringHas(name: 'response_type', value: 'code')
			->assertQueryStringHas(name: 'client_id', value: Config::get('services.stripe.client_id'))
			->assertQueryStringHas(name: 'scope', value: 'read_write')
			->press('Skip this form')
			->pause(5000)
			->assertRouteIs(route: 'backstage.concerts.index');

		$user = $user->fresh();

		expect($user)
			->stripe_account_id->not->toBeNull()
			->stripe_access_token->not->toBeNull();

		$connectedAccount = Account::retrieve(opts: ['api_key' => $user->stripe_access_token]);

		expect($connectedAccount)->id->toEqual($user->stripe_account_id);
	});
});
