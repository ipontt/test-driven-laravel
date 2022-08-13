<?php

namespace App\Http\Livewire;

use App\Models\Concert;
use Lean\LivewireAccess\WithImplicitAccess;
use Lean\LivewireAccess\BlockFrontendAccess;
use Livewire\Component;
use Livewire\Exceptions\PropertyNotFoundException;
use Stripe\PaymentIntent;

use function config;
use function number_format;
use function view;

class TicketCheckout extends Component
{
	use WithImplicitAccess;

	#[BlockFrontAccess]
	public int $original_price;

	public int $quantity = 1;

	protected $rules = [
		'price' => ['required', 'numeric', 'integer'],
		'quantity' => ['required', 'integer', 'min:1'],
	];

	public function __get($property)
	{
		return match ($property) {
			'price'                  => $this->original_price,
			'price_in_dollars'       => number_format(num: $this->price / 100, decimals: 2),
			'total_price'            => $this->price * $this->quantity,
			'total_price_in_dollars' => number_format(num: $this->total_price / 100, decimals: 2),

			default                  => throw new PropertyNotFoundException($property, static::getName()),
		};
	}

	public function createPaymentIntent()
	{
		$payment_intent = PaymentIntent::create(
			params: [
				'amount' => $this->total_price,
				'currency' => 'usd',
				'automatic_payment_methods' => [
					'enabled' => true,
				],
			],
			options: ['api_key' => config('services.stripe.secret')]
		);

		return $payment_intent->client_secret;
	}

	public function render()
	{
		return view('livewire.ticket-checkout');
	}
}
