@extends('layouts.app')

@section('title', 'Your concerts')

@section('header')

<div class="bg-white flex justify-between items-center mx-auto px-2 sm:px-6 lg:px-8">
	<h1 class="max-w-7xl py-4 px-8 text-xl font-bold">
		<span>The Red Chord</span>
		<small class="font-light leading-6">
			<span class="mx-2 select-none">/</span>
			<span>September 13, 2017</span>
		</small>
	</h1>

	<div class="mr-8 space-x-4">
		<span class="text-xl font-bold">Orders</span>
		<a href="{{ url("/backstage/concerts/{$concert->id}/messages/create") }}" class="hover:underline focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-offset-gray-800 p-1">Message attendees</a>
	</div>
</div>

@endsection

@section('content')

<main class="mx-auto max-w-2xl lg:max-w-6xl">
	<section id="overview">
		<h2 class="max-w-7xl py-4 text-lg font-medium text-gray-500">Overview</h2>

		<div class="shadow sm:rounded-md sm:overflow-hidden bg-white space-y-6">
			<div class="px-4 py-5 sm:p-6">
				<div>
					<label for="ticket-sales-progress" class="font-medium text-gray-500">The show is {{ $concert->percentSoldOut() }}% sold out</label>
					<progress id="ticket-sales-progress" value="{{ $concert->ticketsSold() }}" max="{{ $concert->totalTickets() }}">{{ $concert->percentSoldOut() }}</progress>
				</div>
				<dl class="sm:grid sm:grid-cols-3">
					<div class="flex flex-col gap-2 p-6 text-center">
						<dt class="text-lg leading-6 font-medium text-gray-500">Total Tickets Remaining</dt>
						<dd class="text-5xl font-extrabold text-gray-700">{{ $concert->ticketsRemaining() }}</dd>
					</div>
					<div class="flex flex-col gap-2 p-6 text-center">
						<dt class="text-lg leading-6 font-medium text-gray-500">Total Tickets Sold</dt>
						<dd class="text-5xl font-extrabold text-gray-700">{{ $concert->ticketsSold() }}</dd>
					</div>
					<div class="flex flex-col gap-2 p-6 text-center">
						<dt class="text-lg leading-6 font-medium text-gray-500">Total Revenue</dt>
						<dd class="text-5xl font-extrabold text-gray-700">${{ $concert->revenueInDollars() }}</dd>
					</div>
				</dl>
			</div>
		</div>
	</section>
	<section id="recent-orders">
		<h2 class="max-w-7xl py-4 text-lg font-medium text-gray-500">Recent Orders</h2>

		<div class="shadow sm:rounded-md sm:overflow-hidden bg-white space-y-6">
			<div class="px-4 sm:px-6 lg:px-8">
				<div class="flex flex-col">
					<div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
						<div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
							<table class="min-w-full divide-y divide-gray-300">
								<thead>
									<tr>
										<th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 md:pl-0">Email</th>
										<th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900">Tickets</th>
										<th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900">Amount</th>
										<th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900">Card</th>
										<th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900">Purchased</th>
									</tr>
								</thead>
								<tbody class="divide-y divide-gray-200">
									@foreach ($orders as $order)
										<tr>
											<td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 md:pl-0">
												<a href="mailto:{{ $order->email }}" class="hover:text-sky-600">{{ $order->email }}</a>
											</td>
											<td class="whitespace-nowrap py-4 px-3 text-sm text-gray-500">{{ $order->ticketQuantity() }}</td>
											<td class="whitespace-nowrap py-4 px-3 text-sm text-gray-500">${{ $order->amount_in_dollars }}</td>
											<td class="whitespace-nowrap py-4 px-3 text-sm text-gray-500">{{ $order->masked_card_number }}</td>
											<td class="whitespace-nowrap py-4 px-3 text-sm text-gray-500">{{ date('F d, Y @ g:ia') }}</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</main>

@endsection
