<?php

namespace App\Http\Controllers;

use App\Billing\Concerns\PaymentGateway;
use App\Billing\Exceptions\PaymentFailedException;
use App\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use App\Models\Order;
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
            $order = $concert->orderTickets(email: $validated['email'], ticket_quantity: $validated['ticket_quantity']);

            $this->paymentGateway->charge(
                amount: $validated['ticket_quantity'] * $concert->ticket_price,
                token: $validated['payment_token']
            );

            return response()->json(status: Response::HTTP_CREATED);
        } catch (PaymentFailedException $e) {
            $order->cancel();
            return response()->json(status: Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (NotEnoughTicketsException $e) {
            return response()->json(status: Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
