<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\post;

test('logging in with valid credentials succeeds and redirects backstage', function () {
	$user = User::factory()->create([
		'email' => 'jane@example.com',
		'password' => Hash::make('super-secret-password'),
	]);

	$response = post(uri: \route('auth.show-login'), data: [
		'email' => 'jane@example.com',
		'password' => 'super-secret-password',
	]);

	$response->assertRedirect(uri: \route('backstage.concerts.create'));

	$this->assertAuthenticatedAs(user: $user);
});

test('logging in with invalid credentials fails and redirects to login', function () {
	$user = User::factory()->create([
		'email' => 'jane@example.com',
		'password' => Hash::make('super-secret-password'),
	]);

	$response = post(uri: \route('auth.show-login'), data: [
		'email' => 'jane@example.com',
		'password' => 'wrong-password',
	]);

	$response->assertRedirect(uri: \route('auth.show-login'));
	$response->assertSessionHasErrors(keys: ['email' => \trans('auth.failed')]);

	$this->assertGuest();
});

test('logging in with non-existent account fails and redirects to login', function () {
	$response = post(uri: \route('auth.show-login'), data: [
		'email' => 'john@example.com',
		'password' => 'wrong-password',
	]);

	$response->assertRedirect(uri: \route('auth.show-login'));
	$response->assertSessionHasErrors(keys: ['email' => \trans('auth.failed')]);

	$this->assertGuest();
});
