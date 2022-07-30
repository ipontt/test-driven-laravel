<?php

namespace App\Http\Controllers;

use App\Models\Concert;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ConcertController extends Controller
{
	public function show(Concert $concert): Response
	{
		return \response()->view('concerts.show', [
			'concert' => $concert,
		]);
	}
}
