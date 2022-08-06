<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\ImageFile;

use function vsprintf;

class StoreConcertRequest extends FormRequest
{
	protected function prepareForValidation(): void
	{
		$this->merge([
			'date' => vsprintf(format: '%s %s', values: [$this->date, $this->time]),
			'ticket_price' => (int) ((float) $this->ticket_price * 100),
		]);
	}

	public function rules(): array
	{
		return [
			 'title' => ['required'],
			 'subtitle' => ['nullable'],
			 'additional_information' => ['nullable'],
			 'date' => ['required', 'date_format:Y-m-d H:i'],
			 'venue' => ['required'],
			 'venue_address' => ['required'],
			 'city' => ['required'],
			 'state' => ['required'],
			 'zip' => ['required'],
			 'ticket_price' => ['required', 'numeric', 'min:500'],
			 'ticket_quantity' => ['required', 'numeric', 'integer', 'min:1'],
			 'poster_image' => ['nullable', (new ImageFile)->dimensions(Rule::dimensions()->minWidth(400)->ratio(8.5 / 11))],
		];
	}
}
