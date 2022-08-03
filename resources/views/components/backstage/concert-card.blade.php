<li class="col-span-1 bg-white rounded-lg shadow divide-y divide-gray-200">
	<div class="w-full p-6 space-y-4">
		<div>
			<h3 class="truncate text-lg font-bold leading-6 text-gray-700" title="{{ $concert->title }}">{{ $concert->title }}</h1>
			<p class="mt-1 truncate text-md font-bold text-gray-500" title="{{ $concert->subtitle }}">{{ $concert->subtitle }}</p>
		</div>
		<div>
			<div class="flex space-x-4 items-center">
				<x-heroicon-s-location-marker class="w-4 h-4 text-gray-500" />
				<p class="truncate font-medium text-sm text-gray-500 basis-full" title="{{ $concert->venue }} - {{ $concert->city }}">{{ $concert->venue }} - {{ $concert->city }}</p>
			</div>
			<div class="flex space-x-4 items-center">
				<x-heroicon-o-calendar class="w-4 h-4 text-gray-500" />
				<p class="font-medium text-sm text-gray-500">{{ $concert->date->format('M d, Y @ g:ia') }}</p>
			</div>
		</div>
	</div>
	@if ($attributes->get('published'))
		<div class="-mt-px grid grid-cols-1 divide-x divide-gray-200">
			<div class="text-center my-4">
				<a href="{{ route('concerts.show', [$concert]) }}" class="px-4 text-base font-bold text-gray-700 rounded-md border border-transparent shadow-sm focus:ring-2 focus:ring-offset-2 focus:outline-none bg-gray-300 hover:bg-gray-200 focus:ring-gray-400 inline-flex items-center">Get Ticket Link</a>
			</div>
		</div>
	@else
		<div class="-mt-px grid grid-cols-2 divide-x divide-gray-200">
			<div class="text-center my-4">
				<a href="{{ route('backstage.concerts.edit', [$concert]) }}" class="px-4 text-base font-bold text-gray-700 rounded-md border border-transparent shadow-sm focus:ring-2 focus:ring-offset-2 focus:outline-none bg-gray-300 hover:bg-gray-200 focus:ring-gray-400 inline-flex items-center">Edit</a>
			</div>
			<div class="text-center my-4">
				<form action="{{ route('backstage.published_concerts.store') }}" method="POST">
					@csrf
					<input type="hidden" name="concert_id" value="{{ $concert->id }}">					
					<button type="submit" class="px-4 text-base font-bold text-white rounded-md border border-transparent shadow-sm focus:ring-2 focus:ring-offset-2 focus:outline-none bg-sky-600 hover:bg-sky-700 focus:ring-sky-500">Publish</button>
				</form>
			</div>
		</div>
	@endif
</li>
