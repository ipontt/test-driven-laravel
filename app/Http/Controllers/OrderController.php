<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrderController extends Controller
{
    public function show(Order $order): Response
    {
        return \response()->view('orders.show', [
            'order' => $order,
        ]);
    }
}
