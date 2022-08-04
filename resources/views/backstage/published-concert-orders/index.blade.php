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

	<span class="mr-8 text-xl font-bold">Orders</span>
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
</main>

@endsection
