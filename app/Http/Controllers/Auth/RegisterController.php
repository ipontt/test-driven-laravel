<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use function response;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request)
    {
        $invitation = Invitation::query()
            ->whereNull('user_id')
            ->where('code', $request->safe()->invitation_code)
            ->firstOrFail();

        $user = User::create([
            'email' => $request->safe()->email,
            'password' => Hash::make($request->safe()->password),
        ]);

        $invitation->user()->associate($user)->save();

        Auth::login($user);

        return response()->redirectToRoute('backstage.concerts.index', status: Response::HTTP_CREATED);
    }
}
