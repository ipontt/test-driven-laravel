<?php

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConcertRequest;
use App\Http\Requests\UpdateConcertRequest;
use App\Models\Concert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

use function abort_if;
use function response;

class ConcertController extends Controller
{
	public function index(): Response
	{
		[$published_concerts, $unpublished_concerts] = Auth::user()->concerts->partition->isPublished();

		return response()->view('backstage.concerts.index', [
			'published_concerts' => $published_concerts,
			'unpublished_concerts' => $unpublished_concerts,
		]);
	}

	public function create(): Response
	{
		return response()->view('backstage.concerts.create');
	}

	public function store(StoreConcertRequest $request): RedirectResponse
	{
		$concert = Auth::user()
			->concerts()
			->create(attributes: $request->safe()->except(['poster_image']) + [
				'poster_image_path' => $request->safe()->poster_image?->store(path: 'posters', options: ['disk' => 'public']),
			]);

		return response()->redirectToRoute('concerts.show', [$concert]);
	}

	public function edit(Concert $concert): Response
	{
		abort_if(boolean: $concert->isPublished(), code: Response::HTTP_FORBIDDEN);

		return response()->view('backstage.concerts.edit', [
			'concert' => $concert,
		]);
	}

	public function update(UpdateConcertRequest $request, Concert $concert): RedirectResponse
	{
		abort_if(boolean: $concert->isPublished(), code: Response::HTTP_FORBIDDEN);

		$concert->update(attributes: $request->validated());

		return response()->redirectToRoute('backstage.concerts.index');
	}
}
