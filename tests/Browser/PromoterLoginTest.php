<?php

use App\Models\User;
use Laravel\Dusk\Browser;

afterEach(function () {
	$this->browse(fn (Browser $browser) => $browser->logout());
});

test('logging in with valid credentials succeeds and redirects backstage', function () {
	$user = User::factory()->create([
		'email' => 'jane@example.com',
		'password' => Hash::make('super-secret-password'),
	]);

	$this->browse(function (Browser $browser) use ($user) {
		$browser->visitRoute(route: 'auth.show-login')
			->type('email', 'jane@example.com')
			->type('password', 'super-secret-password')
			->press('Log in')
			->assertRouteIs(route: 'backstage.concerts.create');
	});
});

test('logging in with invalid credentials fails and redirects to login', function () {
	$user = User::factory()->create([
		'email' => 'jane@example.com',
		'password' => Hash::make('super-secret-password'),
	]);

	$this->browse(function (Browser $browser) {
		$browser->visitRoute(route: 'auth.show-login')
			->type('email', 'jane@example.com')
			->type('password', 'wrong-password')
			->press('Log in')
			->waitForText(trans('auth.failed'))
			->assertRouteIs(route: 'auth.show-login')
			->assertGuest();
	});
});

test('logging in with non-existent account fails and redirects to login', function () {
	$this->browse(function (Browser $browser) {
		$browser->visitRoute(route: 'auth.show-login')
			->type('email', 'jane@example.com')
			->type('password', 'wrong-password')
			->press('Log in')
			->waitForText(trans('auth.failed'))
			->assertRouteIs(route: 'auth.show-login')
			->assertGuest();
	});
});
