<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
			 'ticket_quantity' => ['required', 'numeric', 'min:1'],
		];
	}
}
