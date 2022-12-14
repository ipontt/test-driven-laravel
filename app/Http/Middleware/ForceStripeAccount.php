<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function redirect;

class ForceStripeAccount
{
	public function handle(Request $request, callable $next)
	{
		if (Auth::user()->stripe_account_id === null) {
			return redirect()->route('backstage.stripe-connect.connect');
		}

		return $next($request);
	}
}
