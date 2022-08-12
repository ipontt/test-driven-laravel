<?php

use App\Http\Middleware\ForceStripeAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;

test('users without a Stripe account are forced to connect with Stripe', function () {
	actingAs(user: User::factory()->create(['stripe_account_id' => null]));

	$middleware = new ForceStripeAccount;

	$response = $middleware->handle(
		request: new Request,
		next: fn () => null,
	);

	expect($response)
		->toBeInstanceOf(RedirectResponse::class)
		->getTargetUrl()->toEqual(route('backstage.stripe-connect.connect'));
});

test('users with a Stripe account can continue', function () {
	actingAs(user: User::factory()->create(['stripe_account_id' => 'test1234']));

	$middleware = new ForceStripeAccount;

	$request = new Request;
	$statefulCallable = new class {
		public bool $wasCalled = false;

		public function __invoke(Request $request)
		{
			$this->wasCalled = true;

			return $request;
		}
	};

	$response = $middleware->handle(
		request: $request,
		next: $statefulCallable,
	);

	expect($statefulCallable)->wasCalled->toBeTrue();
	expect($response)->toBe($request);
});

test('middleware is applied to all backstage routes except for the stripe-connect routes', function () {
	collect(Route::getRoutes()->getIterator())
		->filter(fn ($route) => str($route->action['prefix'])->test('/^(backstage)(?!\/stripe-connect)/'))
		->each(fn ($route) => expect($route)->gatherMiddleware()->toContain(ForceStripeAccount::class));
});

test('middleware is not applied to the stripe-connect routes', function () {
	collect(Route::getRoutes()->getIterator())
		->filter(fn ($route) => str($route->action['prefix'])->test('/(stripe-connect)/'))
    	->each(fn ($route) => expect($route)->gatherMiddleware()->not->toContain(ForceStripeAccount::class));
});