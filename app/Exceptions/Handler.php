<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpFoundation\Response;

use function response;
use function redirect;
use function route;

class Handler extends ExceptionHandler
{
	protected $levels = [
		//
	];

	protected $dontReport = [
		//
	];

	protected $dontFlash = [
		'current_password',
		'password',
		'password_confirmation',
	];

	public function register()
	{
		//
	}

	protected function unauthenticated($request, AuthenticationException $exception)
	{
		return $this->shouldReturnJson($request, $exception)
			? response()->json(['message' => $exception->getMessage()], 401)
			: redirect()->guest($exception->redirectTo() ?? route('auth.login'));
	}
}
