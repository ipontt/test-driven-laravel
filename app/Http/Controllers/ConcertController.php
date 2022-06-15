<?php

namespace App\Http\Controllers;

use App\Models\Concert;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ConcertController extends Controller
{
    public function show(int $id): Response
    {
        $concert = Concert::published()->findOrFail($id);

        return response()->view('concerts.show', [
            'concert' => $concert,
        ]);
    }
}
