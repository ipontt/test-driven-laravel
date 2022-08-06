<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendeeMessageRequest extends FormRequest
{
	public function rules(): array
	{
		return [
			'subject' => ['required'],
			'message' => ['required'],
		];
	}
}
