<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Backstage\ConcertController as BackstageConcertController;
use App\Http\Controllers\Backstage\ConcertMessagesController;
use App\Http\Controllers\Backstage\PublishedConcertOrdersController;
use App\Http\Controllers\Backstage\PublishedConcertsController;
use App\Http\Controllers\Backstage\StripeConnectController;
use App\Http\Controllers\ConcertController;
use App\Http\Controllers\ConcertOrdersController;
use App\Http\Controllers\InvitationsController;
use App\Http\Controllers\OrderController;
use App\Http\Middleware\ForceStripeAccount;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/concerts/{published_concert}', [ConcertController::class, 'show'])->name('concerts.show');

Route::post('/concerts/{published_concert}/orders', [ConcertOrdersController::class, 'store'])->name('concerts.orders.store');

Route::get('/orders/{order:confirmation_number}', [OrderController::class, 'show'])->name('orders.show');

Route::name('auth.')->group(function () {
	Route::view('/login', 'auth.login')->middleware('guest')->name('show-login');

	Route::post('/login', [LoginController::class, 'login'])->middleware('guest')->name('login');
	Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

	Route::post('/register', RegisterController::class)->name('register');
});

Route::prefix('/backstage')->name('backstage.')->middleware(['auth'])->group(function () {
	Route::middleware(ForceStripeAccount::class)->group(function () {
		Route::prefix('/concerts')
			->name('concerts.')
			->controller(BackstageConcertController::class)
			->group(function () {
				Route::get('/', 'index')->name('index');
				Route::get('/create', 'create')->name('create');
				Route::post('/', 'store')->name('store');
				Route::get('/{user_concert}/edit', 'edit')->name('edit');
				Route::patch('/{user_concert}', 'update')->name('update');
			});

		Route::prefix('/published-concerts')
			->name('published-concerts.')
			->controller(PublishedConcertsController::class)
			->group(function () {
				Route::post('/', 'store')->name('store');
			});

		Route::prefix('/published-concerts/{user_published_concert}/orders')
			->name('published-concert-orders.')
			->controller(PublishedConcertOrdersController::class)
			->group(function () {
				Route::get('/', 'index')->name('index');
			});

		Route::prefix('/concerts/{user_concert}/messages')
			->name('concert-messages.')
			->controller(ConcertMessagesController::class)
			->group(function () {
				Route::get('/create', 'create')->name('create');
				Route::post('/', 'store')->name('store');
			});
	});

	Route::prefix('/stripe-connect')
		->name('stripe-connect.')
		->controller(StripeConnectController::class)
		->group(function () {
			Route::get('/connect', 'connect')->name('connect');
			Route::get('/authorize', 'authorizeRedirect')->name('authorize');
			Route::get('/redirect', 'redirect')->name('redirect');
		});
});

Route::get('invitations/{invitation:code}', [InvitationsController::class, 'show'])->name('invitations.show');
