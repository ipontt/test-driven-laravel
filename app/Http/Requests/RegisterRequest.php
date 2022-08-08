<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

use function tap;
use function throw_if;

class RegisterRequest extends FormRequest
{
	public function rules(): array
	{
		return [
			'email' => ['required', 'email', Rule::unique(User::class, 'email')],
			'password' => ['required', Password::min(8)],
			'invitation_code' => ['required', 'uuid'],
		];
	}

	public function withValidator($validator): void
	{
		$validator->after(fn (Validator $validator) => throw_if(
			condition: Arr::has(array: $validator->failed(), keys: 'invitation_code.Uuid'),
			exception: tap(new ModelNotFoundException)->setModel(
				model: Invitation::class,
				ids: $validator->getData()['invitation_code'],
			),
		));
	}
}
