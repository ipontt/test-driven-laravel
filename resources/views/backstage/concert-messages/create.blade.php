@extends('layouts.app')

@section('title', 'Your concerts')

@section('header')

<div class="bg-white flex justify-between items-center mx-auto px-2 sm:px-6 lg:px-8">
	<h1 class="max-w-7xl py-4 px-8 text-xl font-bold">
		<span>The Red Chord</span>
		<small class="font-light leading-6">
			<span class="mx-2">/</span>
			<span>September 13, 2017</span>
		</small>
	</h1>

	<div class="mr-8 space-x-4">
		<span class="text-xl font-bold">Orders</span>
		<a href="{{ route('backstage.concert-messages.create', [$concert]) }}" class="hover:underline focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-offset-gray-800 p-1">Message attendees</a>
	</div>
</div>

@endsection

@section('content')

<main class="mx-auto max-w-2xl">
	<section id="message-form">
		<h2 class="max-w-7xl py-4 text-lg text-center font-medium text-gray-500">New Message</h2>
		<form action="" method="POST">
			@csrf
			<div class="shadow sm:rounded-md sm:overflow-hidden bg-white space-y-6">
				<div class="px-4 py-5 sm:p-6 space-y-4">
					<div class="space-y-2">
						<label for="subject" class="block text-sm font-bold text-gray-700">Subject</label>
						<input type="text" name="subject" id="subject" class="mt-1 focus:ring-sky-500 focus:border-sky-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
					</div>
					<div class="space-y-2">
						<label for="message" class="block text-sm font-bold text-gray-700">Message</label>
						<textarea id="message" name="message" rows="3" class="shadow-sm focus:ring-sky-500 focus:border-sky-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md"></textarea>
					</div>

					<button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-bold rounded-md text-white bg-sky-600 hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">Send now</button>
				</div>
			</div>
		</form>
	</section>
</main>

@endsection
