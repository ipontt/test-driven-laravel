<div class="bg-white overflow-hidden sm:rounded-lg divide-y divide-gray-200 drop-shadow-xl">
	<div class="bg-gray-700 px-4 py-4 sm:px-6 flex justify-between">
		<div>
			<h3 class="text-2xl leading-6 font-bold text-gray-200">{{ $ticket->concert->title }}</h3>
			<p class="mt-1 text-lg font-bold text-gray-400">{{ $ticket->concert->subtitle }}</p>
		</div>
		<div class="text-right">
			<p class="text-md font-bold text-gray-200">General Admission</p>
			<p class="text-md font-medium text-gray-200">Admit One</p>
		</div>
	</div>
	<div class="px-4 py-4 sm:px-6 grid grid-cols-2 gap-4">
		<div class="flex space-x-4 items-flex-start">
			<x-heroicon-o-calendar class="w-6 h-6 text-sky-500" />
			<div>
				<p class="font-bold text-gray-700">
					<time datetime="{{ $ticket->concert->date->toDateTimeString() }}">
						{{ $ticket->concert->date->format('l, F d, Y') }}
					</time>
				</p>
				<p class="text-gray-500">Doors at {{ $ticket->concert->formatted_start_time }}</p>
			</div>
		</div>
		<div class="flex space-x-4 items-flex-start">
			<x-heroicon-s-location-marker class="w-6 h-6 text-sky-500" />
			<div>
				<p class="font-bold text-gray-700">{{ $ticket->concert->venue }}</p>
				<p class="text-gray-500">{{ $ticket->concert->venue_address }}</p>
				<p class="text-gray-500">{{ $ticket->concert->city }}, {{ $ticket->concert->state }} {{ $ticket->concert->zip }}</p>
			</div>
		</div>
	</div>
	<div class="px-4 py-4 sm:px-6 flex justify-between">
		<div>
			<p class="text-xl font-light text-gray-700">{{ $ticket->code }}</p>
		</div>
		<div class="text-right">
			<p class="text-md font-light text-gray-700">{{ $email }}</p>
		</div>
	</div>
</div>
