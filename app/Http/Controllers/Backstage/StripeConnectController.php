<?php

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Stripe\OAuth;
use Stripe\StripeClient;

use function http_build_query;
use function response;
use function vsprintf;

class StripeConnectController extends Controller
{
	public function __construct()
	{
		$this->stripe = new StripeClient(Config::get('services.stripe.secret'));
	}

	public function connect(): Response
	{
		return response()->view('backstage.stripe-connect.connect');
	}

	public function authorizeRedirect(): RedirectResponse
	{
		$url = vsprintf(format: '%s?%s', values: [
			'https://connect.stripe.com/oauth/v2/authorize',
			http_build_query(data: [
				'response_type' => 'code',
				'client_id' => Config::get('services.stripe.client_id'),
				'scope' => 'read_write',
			]),
		]);

		return response()->redirectAway(path: $url);
	}

	public function redirect(Request $request): RedirectResponse
	{
		$response = $this->stripe->oauth->token(params: [
			'grant_type' => 'authorization_code',
			'code' => $request->code,
		]);

		Auth::user()->update([
			'stripe_account_id' => $response->stripe_user_id,
			'stripe_access_token' => $response->access_token,
		]);

		return response()->redirectToRoute(route: 'backstage.concerts.index');
	}
}
