@extends('layouts.app')

@section('title', 'Add a concert')

@section('content')

<main class="mx-auto max-w-2xl lg:max-w-4xl">
	<form action="{{ route('backstage.concerts.update', [$concert]) }}" method="POST">
		@csrf
		@method('PATCH')
		<div>
			<div class="md:grid md:grid-cols-3 md:gap-6">
				<div class="md:col-span-1">
					<div class="px-4 sm:px-0">
						<h3 class="text-lg font-medium leading-6 text-gray-900">Concert Details</h3>
						<p class="mt-1 text-sm text-gray-600">Tell us who's playing! <em>(Please be Slayer!)</em></p>
						<p class="mt-4 text-sm text-gray-600">Include the headliner in the concert name, use the subtitle section to list any opening bands, and add any important information to the description.</p>
					</div>
				</div>
				<div class="mt-5 md:mt-0 md:col-span-2">
					<div class="shadow sm:rounded-md sm:overflow-hidden">
						<div class="px-4 py-5 bg-white space-y-6 sm:p-6">
							<div class="grid grid-cols-3 gap-6">
								<div class="col-span-3">
									<label for="title" class="block text-sm font-medium text-gray-700">Title</label>
									<input type="text" name="title" id="title" placeholder="The Headliners" class="mt-1 focus:ring-sky-500 focus:border-sky-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $concert->title }}">
								</div>
							</div>

							<div class="grid grid-cols-3 gap-6">
								<div class="col-span-3">
									<label for="subtitle" class="block text-sm font-medium text-gray-700">Subtitle</label>
									<input type="text" name="subtitle" id="subtitle" placeholder="With the Openers (optional)" class="mt-1 focus:ring-sky-500 focus:border-sky-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $concert->subtitle }}">
								</div>
							</div>

							<div>
								<label for="additional_information" class="block text-sm font-medium text-gray-700">Additional Information</label>
								<div class="mt-1">
									<textarea id="additional_information" name="additional_information" rows="3" class="shadow-sm focus:ring-sky-500 focus:border-sky-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md" placeholder="This concert is 19+. (optional)">{{ $concert->additional_information }}</textarea>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="hidden sm:block" aria-hidden="true">
			<div class="py-5">
				<div class="border-t border-gray-200"></div>
			</div>
		</div>

		<div class="mt-10 sm:mt-0">
			<div class="md:grid md:grid-cols-3 md:gap-6">
				<div class="md:col-span-1">
					<div class="px-4 sm:px-0">
						<h3 class="text-lg font-medium leading-6 text-gray-900">Date & Time</h3>
						<p class="mt-1 text-sm text-gray-600">True metalheads only care about the obscure openers, so make sure they don't get there late!</p>
					</div>
				</div>
				<div class="mt-5 md:mt-0 md:col-span-2">
					<div class="shadow sm:rounded-md sm:overflow-hidden">
						<div class="px-4 py-5 bg-white space-y-6 sm:p-6">
							<div class="grid grid-cols-3 gap-6">
								<div class="col-span-3">
									<label for="date" class="block text-sm font-medium text-gray-700">Date</label>
									<input type="date" name="date" id="date" class="mt-1 focus:ring-sky-500 focus:border-sky-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $concert->date->format('Y-m-d') }}">
								</div>
							</div>

							<div class="grid grid-cols-3 gap-6">
								<div class="col-span-3">
									<label for="time" class="block text-sm font-medium text-gray-700">Start Time</label>
									<input type="time" name="time" id="time" class="mt-1 focus:ring-sky-500 focus:border-sky-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $concert->date->format('H:i') }}">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="hidden sm:block" aria-hidden="true">
			<div class="py-5">
				<div class="border-t border-gray-200"></div>
			</div>
		</div>

		<div class="mt-10 sm:mt-0">
			<div class="md:grid md:grid-cols-3 md:gap-6">
				<div class="md:col-span-1">
					<div class="px-4 sm:px-0">
						<h3 class="text-lg font-medium leading-6 text-gray-900">Venue Information</h3>
						<p class="mt-1 text-sm text-gray-600">Where's the show? Let attendees know the venue name and address so they can bring the mosh.</p>
					</div>
				</div>
				<div class="mt-5 md:mt-0 md:col-span-2">
					<div class="shadow sm:rounded-md sm:overflow-hidden">
						<div class="px-4 py-5 bg-white space-y-6 sm:p-6">
							<div class="grid grid-cols-3 gap-6">
								<div class="col-span-3">
									<label for="venue" class="block text-sm font-medium text-gray-700">Venue</label>
									<input type="text" name="venue" id="venue" placeholder="The Mosh Pit" class="mt-1 focus:ring-sky-500 focus:border-sky-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $concert->venue }}">
								</div>
							</div>

							<div class="grid grid-cols-3 gap-6">
								<div class="col-span-3">
									<label for="venue-address" class="block text-sm font-medium text-gray-700">Street Address</label>
									<input type="text" name="venue_address" id="venue-address" placeholder="500 Example Ave." class="mt-1 focus:ring-sky-500 focus:border-sky-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $concert->venue_address }}">
								</div>
							</div>

							<div class="grid grid-cols-3 gap-6">
								<div>
									<label for="city" class="block text-sm font-medium text-gray-700">City</label>
									<input type="text" name="city" id="city" placeholder="Laraville" class="mt-1 focus:ring-sky-500 focus:border-sky-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $concert->city }}">
								</div>
								<div>
									<label for="state" class="block text-sm font-medium text-gray-700">State/Province</label>
									<input type="text" name="state" id="state" placeholder="ON" class="mt-1 focus:ring-sky-500 focus:border-sky-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $concert->state }}">
								</div>
								<div>
									<label for="zip" class="block text-sm font-medium text-gray-700">ZIP</label>
									<input type="text" name="zip" id="zip" placeholder="90210" class="mt-1 focus:ring-sky-500 focus:border-sky-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $concert->zip }}">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="hidden sm:block" aria-hidden="true">
			<div class="py-5">
				<div class="border-t border-gray-200"></div>
			</div>
		</div>

		<div class="mt-10 sm:mt-0">
			<div class="md:grid md:grid-cols-3 md:gap-6">
				<div class="md:col-span-1">
					<div class="px-4 sm:px-0">
						<h3 class="text-lg font-medium leading-6 text-gray-900">Tickets & Pricing</h3>
						<p class="mt-1 text-sm text-gray-600">Set your ticket price and availability, but don't forget, metalheads are cheap so keep it reasonable.</p>
					</div>
				</div>
				<div class="mt-5 md:mt-0 md:col-span-2">
					<div class="shadow sm:rounded-md sm:overflow-hidden">
						<div class="px-4 py-5 bg-white space-y-6 sm:p-6">
							<div class="grid grid-cols-2 gap-6">
								<div>
									<label for="ticket-price" class="block text-sm font-medium text-gray-700">Price</label>
									<div class="mt-1 flex rounded-md shadow-sm">
										<span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">$</span>
										<input type="text" name="ticket_price" id="ticket-price" placeholder="0.00" pattern="/[0-9]+\.[0-9]{2}/" class="focus:ring-sky-500 focus:border-sky-500 flex-1 block w-full rounded-none rounded-r-md sm:text-sm border-gray-300" value="{{ $concert->ticket_price_in_dollars }}">
									</div>
								</div>
								<div>
									<label for="ticket_quantity" class="block text-sm font-medium text-gray-700">Tickets Available</label>
									<input type="number" name="ticket-quantity" id="ticket_quantity" class="mt-1 focus:ring-sky-500 focus:border-sky-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" min="1" value="{{ $concert->tickets()->count() }}">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="hidden sm:block" aria-hidden="true">
			<div class="py-5">
				<div class="border-t border-gray-200"></div>
			</div>
		</div>

		<div class="mt-10 sm:mt-0 flex justify-end">
			<button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-sky-600 hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">Save</button>
		</div>
	</form>
</main>

@endsection
