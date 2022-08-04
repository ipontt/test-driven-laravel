<?php

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Controller;
use App\Models\Concert;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use function response;

class PublishedConcertOrdersController extends Controller
{
	public function index(Concert $concert): Response
	{
		$concert->loadCount('tickets');

		return response()->view(view: 'backstage.published-concert-orders.index', data: [
			'concert' => $concert,
		]);
	}
}
