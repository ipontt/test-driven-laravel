<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use function abort_if;

class InvitationsController extends Controller
{
	public function show(Invitation $invitation): Response
	{
		abort_if(boolean: $invitation->hasBeenUsed(), code: Response::HTTP_NOT_FOUND);

		return response()->view('invitations.show', [
			'invitation' => $invitation,
		]);
	}
}
