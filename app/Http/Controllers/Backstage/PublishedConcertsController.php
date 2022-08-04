<?php

namespace App\Http\Controllers\Backstage;

use App\Exceptions\ConcertAlreadyPublishedException;
use App\Http\Controllers\Controller;
use App\Models\Concert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

use function abort;
use function response;

class PublishedConcertsController extends Controller
{
	public function store(Request $request): RedirectResponse
	{
		$concert = Auth::user()->concerts()->findOrFail($request->concert_id);

		try {
			$concert->publish();
		} catch (ConcertAlreadyPublishedException $e) {
			abort(code: Response::HTTP_UNPROCESSABLE_ENTITY);
		}

		return response()->redirectToRoute('backstage.concerts.index', status: Response::HTTP_CREATED);
	}
}
