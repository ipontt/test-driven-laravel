<?php

namespace App\Http\Controllers;

use App\Billing\Concerns\PaymentGateway;
use App\Billing\Exceptions\PaymentFailedException;
use App\Exceptions\NotEnoughTicketsException;
use App\Http\Resources\OrderResource;
use App\Models\Concert;
use App\Models\Order;
use App\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\{Response, JsonResponse};

class ConcertOrdersController extends Controller
{
	public function __construct(private PaymentGateway $paymentGateway) { }

	public function store(Request $request, int $id): JsonResponse
	{
		$concert = Concert::published()->findOrFail($id);

		$validated = $request->validate([
			'email' => ['required', 'email'],
			'ticket_quantity' => ['required', 'integer', 'min:1'],
			'payment_token' => ['required', 'string'],
		]);

		try {
			// Find some tickets
			$tickets = $concert->reserveTickets(quantity: $validated['ticket_quantity']);
			$reservation = Reservation::for(tickets: $tickets);

			// Charge a customer for the tickets
			$this->paymentGateway->charge(
				amount: $reservation->totalCost(),
				token: $validated['payment_token'],
			);

			// Create an order for those tickets
			$order = Order::forTickets(tickets: $tickets, email: $validated['email'], amount: $reservation->totalCost());

			return response()->json(data: OrderResource::make($order), status: Response::HTTP_CREATED);
		} catch (NotEnoughTicketsException|PaymentFailedException $e) {
			return response()->json(status: Response::HTTP_UNPROCESSABLE_ENTITY);
		}
	}
}
