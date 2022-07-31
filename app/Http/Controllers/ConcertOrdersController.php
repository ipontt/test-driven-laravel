<?php

namespace App\Http\Controllers;

use App\Billing\Concerns\PaymentGateway;
use App\Billing\Exceptions\PaymentFailedException;
use App\Exceptions\NotEnoughTicketsException;
use App\Http\Resources\OrderResource;
use App\Mail\OrderConfirmationEmail;
use App\Models\Concert;
use App\Models\Order;
use App\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

use function response;

class ConcertOrdersController extends Controller
{
	public function __construct(private PaymentGateway $paymentGateway) {}

	public function store(Request $request, Concert $concert): JsonResponse
	{
		$validated = $request->validate([
			'email' => ['required', 'email'],
			'ticket_quantity' => ['required', 'integer', 'min:1'],
			'payment_token' => ['required', 'string'],
		]);

		try {
			$reservation = $concert->reserveTickets(
				quantity: $validated['ticket_quantity'],
				email: $validated['email'],
			);

			$order = $reservation->complete(
				paymentGateway: $this->paymentGateway,
				paymentToken: $validated['payment_token'],
			);

			Mail::to($order->email)->send(new OrderConfirmationEmail(order: $order));

			return response()->json(status: Response::HTTP_CREATED, data: OrderResource::make($order));
		} catch (PaymentFailedException $e) {
			$reservation->cancel();
			return response()->json(status: Response::HTTP_UNPROCESSABLE_ENTITY);
		} catch (NotEnoughTicketsException $e) {
			return response()->json(status: Response::HTTP_UNPROCESSABLE_ENTITY);
		}
	}
}
