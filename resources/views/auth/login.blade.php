@extends('layouts.app')

@section('content')

<main class="mx-auto max-w-2xl lg:max-w-4xl">
	<div class="bg-white min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 drop-shadow-xl">
		<div class="max-w-md w-full space-y-8">
			<div>
				<img class="mx-auto h-12 w-auto" src="{{ \asset('small-logo-pest.png') }}" alt="Logo">
				<h2 class="mt-6 text-center text-3xl font-extrabold text-gray-700">Sign in to your account</h2>
			</div>
			<form class="mt-8 space-y-6" action="{{ \route('auth.login') }}" method="POST">
				@csrf
				<div class="rounded-md shadow-sm -space-y-px">
					<div>
						<label for="email" class="sr-only">Email address</label>
						<input
							id="email"
							name="email"
							type="email"
							autocomplete="email"
							required
							placeholder="Email address"
							value="{{ \old('email') }}"
							@class([
								'appearance-none rounded-none relative block w-full px-3 py-2 rounded-t-md border focus:z-10 focus:outline-none sm:text-sm ',
								'border-red-300 placeholder-red-500 text-red-700 focus:ring-red-500 focus:border-red-500' => $errors->has('email'),
								'border-gray-300 placeholder-gray-500 text-gray-700 focus:ring-sky-500 focus:border-sky-500' => ! $errors->has('email'),
							])
						>
					</div>
					<div>
						<label for="password" class="sr-only">Password</label>
						<input id="password" name="password" type="password" autocomplete="current-password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-700 rounded-b-md focus:outline-none focus:ring-sky-500 focus:border-sky-500 focus:z-10 sm:text-sm" placeholder="Password">
					</div>
				</div>

				<div>
					<button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-sky-600 hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
						<span class="absolute left-0 inset-y-0 flex items-center pl-3">
							<x-heroicon-s-lock-closed class="h-5 w-5 text-sky-500 group-hover:text-sky-400" />
						</span>
						Log in
					</button>
				</div>

				<div class="relative">
					<div class="absolute inset-0 flex items-center" aria-hidden="true">
						<div class="w-full border-t border-gray-300"></div>
					</div>
					<div class="relative flex justify-center">
						@error('email')
							<span class="px-2 bg-white text-sm text-red-600">{{ $message }}</span>
						@enderror
					</div>
				</div>
			</form>
		</div>
	</div>
</main>

@endsection
