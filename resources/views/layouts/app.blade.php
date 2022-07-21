<!DOCTYPE html>
<html lang="{{ str_replace(subject: app()->getLocale(), search: '_', replace: '-') }}">
<head>
	<meta charset="UTF-8">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>@yield('title', 'TicketBeast')</title>
	@vite(['resources/css/app.css', 'resources/js/app.js'])
	@stack('head-scripts')
	@stack('styles')
</head>
<body class="font-sans antialiased bg-gray-200">
	<div class="bg-gray-100">
		<div class="py-12 px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
			<div class="mx-auto max-w-2xl lg:max-w-4xl">
				@yield('content')
			</div>
		</div>
	</div>
	@stack('body-scripts')
</body>
</html>