<?php

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendeeMessageRequest;
use App\Jobs\SendAttendeeMessage;
use App\Models\AttendeeMessage;
use App\Models\Concert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use function response;

class ConcertMessagesController extends Controller
{
	public function create(Concert $concert): Response
	{
		return response()->view('backstage.concert-messages.create', [
			'concert' => $concert,
		]);
	}

	public function store(StoreAttendeeMessageRequest $request, Concert $concert): RedirectResponse
	{
		$message = $concert->attendeeMessages()->create($request->validated());

		SendAttendeeMessage::dispatch(attendeeMessage: $message);

		return response()
			->redirectToRoute('backstage.concert-messages.create', [$concert], status: Response::HTTP_CREATED)
			->with('flash', 'Your message has been sent.');
	}
}
