<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ConcertController;
use App\Http\Controllers\Backstage\ConcertController as BackstageConcertController;
use App\Http\Controllers\ConcertOrdersController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/concerts/{published_concert}', [ConcertController::class, 'show'])->name('concerts.show');
Route::post('/concerts/{published_concert}/orders', [ConcertOrdersController::class, 'store'])->name('concerts.orders.store');

Route::get('/orders/{order:confirmation_number}', [OrderController::class, 'show'])->name('orders.show');

Route::name('auth.')->group(function () {
	Route::view('/login', 'auth.login')->middleware('guest')->name('show-login');
	Route::post('/login', [LoginController::class, 'login'])->middleware('guest')->name('login');
	Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');
});

Route::prefix('/backstage')->name('backstage.')->middleware(['auth'])->group(function () {
	Route::prefix('/concerts')->name('concerts.')->controller(BackstageConcertController::class)->group(function () {
		Route::get('/', 'index')->name('index');
		Route::get('/create', 'create')->name('create');
		Route::post('/', 'store')->name('store');
		Route::get('/{user_concert}/edit', 'edit')->name('edit');
		Route::patch('/{user_concert}', 'update')->name('update');
	});
});

/*
Route::get('success', function (\Illuminate\Http\Request $request) {
	dump(
		$request->headers->all(),
		$request->query->all(),
		$request->attributes->all(),
		$request->request->all(),
		$request->cookies->all(),
	);

	Log::debug('Into Success View');

	return view('stripe-test.success');
});
Route::view('checkout', 'stripe-test.checkout');
Route::view('cancel', 'stripe-test.cancel');

Route::get('checkout2', function (\Illuminate\Http\Request $request) {
	\Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
	$session = Stripe\Checkout\Session::create([
		'payment_method_types' => ['card'],
		'line_items' => [
			[
				'price_data' => [
					'currency' => 'usd',
					'product_data' => [
						'name' => 'Ticket',
					],
					'unit_amount' => 2000,
				],
				'quantity' => 2,
			],
		],
		'mode' => 'payment',
		'success_url' => 'http://localhost:8000/success?session_id={CHECKOUT_SESSION_ID}',
		'cancel_url' => 'http://localhost:8000/cancel?session_id={CHECKOUT_SESSION_ID}',
	]);

	Log::debug('session');
	Log::debug($session);
	Log::debug('redirect');

	return response()->redirectTo(path: $session->url, status: 303);
});

Route::post('webhook', function (\Illuminate\Http\Request $request) {

	// This is your Stripe CLI webhook secret for testing your endpoint locally.
	$endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

	$payload = @file_get_contents('php://input');
	Log::debug('raw php//input payload');
	Log::debug($payload);

	$sig_header = $request->server('HTTP_STRIPE_SIGNATURE');
	$event = null;

	try {
		$event = \Stripe\Webhook::constructEvent(
			$payload, $sig_header, $endpoint_secret
		);
	} catch(\UnexpectedValueException $e) {
  		// Invalid payload
  		Log::debug($e);
  		http_response_code(400);
  		exit();
	} catch(\Stripe\Exception\SignatureVerificationException $e) {
  		// Invalid signature
  		Log::debug($e);
  		http_response_code(400);
  		exit();
	}

	// Handle the event
	switch ($event->type) {
		case 'payment_intent.created':
			Log::debug('payment_intent.created');
			$paymentIntent = $event->data->object;
			Log::debug($paymentIntent);
			break;

		case 'customer.created':
			Log::debug('customer.created');
			$paymentIntent = $event->data->object;
			Log::debug($paymentIntent);
			break;

		case 'payment_intent.succeeded':
			Log::debug('payment_intent.succeeded');
			$paymentIntent = $event->data->object;
			Log::debug($paymentIntent);
			break;

		case 'charge.succeeded':
			Log::debug('charge.succeeded');
			$paymentIntent = $event->data->object;
			Log::debug($paymentIntent);
			break;

		case 'checkout.session.completed':
			Log::debug('checkout.session.completed');
			$paymentIntent = $event->data->object;
			Log::debug($paymentIntent);
			break;
  		// ... handle other event types
  		default:
    		echo 'Received unknown event type ' . $event->type;
    		Log::debug('Received unknown event type ' . $event->type);
	}

	Log::debug('out of webhook handler');

	http_response_code(200);
});

Route::view('test', 'test');
Route::post('test', function () {
	return [
		'html' => '<div>some html</html>',
		'snapshot' => json_encode(['class' => 'Some\\Namespace\\Class', 'data' => ['count' => 1]]),
	];
});
*/
