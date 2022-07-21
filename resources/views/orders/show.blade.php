@extends('layouts.app')

@section('content')

<div class="overflow-hidden bg-white rounded-lg shadow divide-y divide-gray-200">
	<div class="py-5 px-4 sm:p-6 flex justify-between">
		<h1 class="text-2xl font-light leading-6 text-gray-700">Order Summary</h1>
		<span id="confirmation-number" class="text-sky-500">{{ $order->confirmation_number }}</a>
	</div>
	<div class="py-5 px-4 sm:p-6">
		<div>
			<h2 class="text-lg font-bold leading-6 text-gray-700">Order Total: ${{ $order->amount_in_dollars }}</h2>
			<p class="mt-1 text-md font-light text-gray-500">Billed to card #: {{ $order->maked_card_number }}.</p>
		</div>
	</div>
	<div class="py-5 px-4 sm:p-6">
		<h2 class="text-xl font-light leading-6 text-gray-700">Your Tickets</h2>
		<div class="space-y-8 mt-8">
			@foreach ($order->tickets as $ticket)
				<x-purchased-ticket :ticket="$ticket" :email="$order->email" />
			@endforeach
		</div>

	</div>
</div>

@endsection
