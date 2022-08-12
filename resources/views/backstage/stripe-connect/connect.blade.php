@extends('layouts.app')

@section('content')

<main class="mx-auto max-w-2xl">
	<div class="bg-white min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 drop-shadow-xl">
		<div class="max-w-md w-full space-y-8">
			<div>
				<img class="mx-auto h-16 w-auto" src="{{ asset('stripe-logo.png') }}" alt="Stripe Logo">
				<h2 class="mt-6 text-center text-3xl font-extrabold text-gray-700">Connect your Stripe Account</h2>
			</div>
			
			<p class="text-sm text-center text-gray-700">Good news, TicketBeast now integrates directly with your Stripe account!</p>

			<p class="text-sm text-center text-gray-700">To continue, connect your Stripe account by clicking on the button below:</p>

			<a href="{{ route('backstage.stripe-connect.authorize') }}" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-sky-600 hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">Connect with Stripe</a>
		</div>
	</div>
</main>

@endsection
