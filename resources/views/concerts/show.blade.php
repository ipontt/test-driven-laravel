@extends('layouts.app')

@section('content')

<div class="overflow-hidden bg-white rounded-lg shadow divide-y divide-gray-200">
	<div class="py-5 px-4 space-y-6 sm:p-6">
		<div>
			<h1 class="text-3xl font-bold leading-6 text-gray-700">{{ $concert->title }}</h1>
			<p class="mt-1 text-lg font-bold text-gray-500">{{ $concert->subtitle }}</p>
		</div>
		<div class="flex space-x-1 items-flex-start">
			<x-heroicon-o-calendar class="w-6 h-6 text-gray-700" />
			<p class="font-bold text-gray-700">{{ $concert->formatted_date }}</p>
		</div>
		<div class="flex space-x-1 items-flex-start">
			<x-heroicon-o-clock class="w-6 h-6 text-gray-700" />
			<p class="font-bold text-gray-700">Doors at {{ $concert->formatted_start_time }}</p>
		</div>
		<div class="flex space-x-1 items-flex-start">
			<x-heroicon-s-currency-dollar class="w-6 h-6 text-gray-700" />
			<p class="font-bold text-gray-700">{{ $concert->ticket_price_in_dollars }}</p>
		</div>
		<div class="flex space-x-1 items-flex-start">
			<x-heroicon-s-location-marker class="w-6 h-6 text-gray-700" />
			<div>
				<p class="font-bold text-gray-700">{{ $concert->venue }}</p>
				<p class="text-gray-500">{{ $concert->venue_address }}</p>
				<p class="text-gray-500">{{ $concert->city }}, {{ $concert->state }} {{ $concert->zip }}</p>
			</div>
		</div>
		<div class="flex space-x-1 items-flex-start">
			<x-heroicon-s-information-circle class="w-6 h-6 text-gray-700" />
			<div>
				<p class="font-bold text-gray-700">Additional information</p>
				<p class="text-gray-500">{{ $concert->additional_information }}</p>
			</div>
		</div>
	</div>
	<div class="py-5 px-4 sm:p-6">
		<x-ticket-checkout :concert="$concert" />
	</div>
</div>

@endsection
