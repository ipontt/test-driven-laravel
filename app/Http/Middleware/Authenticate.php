<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

use function route;

class Authenticate extends Middleware
{
	/* Get the path the user should be redirected to when they are not authenticated. */
	protected function redirectTo($request): ?string
	{
		if (! $request->expectsJson()) {
			return route('auth.show-login');
		}
	}
}
