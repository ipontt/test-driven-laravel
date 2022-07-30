<!DOCTYPE html>
<html lang="{{ \str_replace(subject: \app()->getLocale(), search: '_', replace: '-') }}">
<head>
	<meta charset="UTF-8">
	<meta name="csrf-token" content="{{ \csrf_token() }}">
	<link rel="icon" href="{{ \asset('favicon.ico') }}">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>@yield('title', 'TicketBeast')</title>
	@vite(['resources/css/app.css', 'resources/js/app.js'])
	@stack('head-scripts')
	@stack('styles')
</head>
<body class="font-sans antialiased bg-gray-200">
	@auth
		<nav class="bg-gray-800">
			<div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
				<div class="relative flex items-center justify-between h-16">
					<div class="flex-1 flex items-center justify-center sm:items-stretch sm:justify-start">
						<div class="flex-shrink-0 flex items-center">
							<img class="block sm:hidden h-8 w-auto" src="{{ \asset('favicon.ico') }}" alt="Logo">
							<img class="hidden sm:block h-8 w-auto" src="{{ \asset('small-logo-pest.png') }}" alt="Logo">
						</div>
					</div>
					<div class="absolute inset-y-0 right-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">
						<form action="{{ \route('auth.logout') }}" method="POST">
							@csrf
							<button type="submit" class="bg-gray-800 p-1 text-gray-100 font-bold hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white">Log out</button>
						</form>
					</div>
				</div>
			</div>
		</nav>
		<div class="bg-white">
			<h1 class="max-w-7xl mx-auto py-4 px-8 text-xl font-light">@yield('title', '')</h1>
		</div>
	@endauth

	<div class="bg-gray-100 min-h-screen border border-b-gray-200">
		<div class="py-12 px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
			<div class="mx-auto max-w-2xl lg:max-w-4xl">
				@yield('content')
			</div>
		</div>
	</div>
	@stack('body-scripts')
</body>
</html>