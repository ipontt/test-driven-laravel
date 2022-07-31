<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

use function response;
use function trans;

class LoginController extends Controller
{
	public function login(Request $request): RedirectResponse
	{
		if (! Auth::attempt(credentials: $request->only(keys: ['email', 'password']))) {
			return response()
				->redirectToRoute('auth.show-login')
				->withInput()
				->withErrors(['email' => [trans('auth.failed')]]);
		}

		return response()->redirectToRoute('backstage.concerts.create');
	}

	public function logout(Request $request): RedirectResponse
	{
		Auth::logout();
		$request->session()->invalidate();
		$request->session()->regenerateToken();

		return response()->redirectToRoute('auth.show-login');
	}
}
