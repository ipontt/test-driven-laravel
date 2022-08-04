@extends('layouts.app')

@section('title', 'Your concerts')

@section('header')

<div class="bg-white flex justify-between items-center mx-auto px-2 sm:px-6 lg:px-8">
	<h1 class="max-w-7xl py-4 px-8 text-xl font-light">Your concerts</h1>

	<a href="{{ route('backstage.concerts.create') }}" class="mr-8 px-4 text-base font-bold text-white rounded-md border border-transparent shadow-sm focus:ring-2 focus:ring-offset-2 focus:outline-none bg-sky-600 hover:bg-sky-700 focus:ring-sky-500 inline-flex items-center">Add a concert</a>
</div>

@endsection

@section('content')

<main class="mx-auto max-w-2xl lg:max-w-6xl">
	<section id="published-concerts">
		<h2 class="max-w-7xl py-4 text-lg font-medium text-gray-500">Published</h2>

		<ul role="list" class="mx-2 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
			@foreach ($published_concerts as $concert)
				<x-backstage.concert-card :concert="$concert" published />
			@endforeach
		</ul>
	</section>

	<section id="draft-concerts">
		<h2 class="max-w-7xl py-4 text-lg font-medium text-gray-500">Draft</h2>

		<ul role="list" class="mx-2 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
			@foreach ($unpublished_concerts as $concert)
				<x-backstage.concert-card :concert="$concert" />
			@endforeach
		</ul>
	</section>
</main>

@endsection
