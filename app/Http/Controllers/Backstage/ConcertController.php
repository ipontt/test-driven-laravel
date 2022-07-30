<?php

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConcertRequest;
use App\Models\Concert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ConcertController extends Controller
{
	public function create(): Response
	{
		return \response()->view('concerts.create');
	}

	public function store(StoreConcertRequest $request): RedirectResponse
	{
		$concert = Auth::user()
			->concerts()
			->create(attributes: $request->safe()->except('ticket_quantity'))
			->addTickets(quantity: $request->validated('ticket_quantity'))
			->publish();

		return \response()->redirectToRoute('concerts.show', [$concert]);
	}
}
