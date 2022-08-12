<?php

namespace App\Providers;

use App\Models\Concert;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

use function base_path;
use function response;

class RouteServiceProvider extends ServiceProvider
{
	public function boot(): void
	{
		Route::bind('user_concert', function ($concert_id) {
			return Auth::authenticate()->concerts()->findOrFail($concert_id);
		});

		Route::bind('published_concert', function ($concert_id) {
			return Concert::published()->findOrFail($concert_id);
		});

		Route::bind('user_published_concert', function ($concert_id) {
			return Auth::authenticate()->concerts()->published()->findOrFail($concert_id);
		});

		$this->routes(function () {
			Route::middleware('api')
				->prefix('api')
				->group(base_path('routes/api.php'));

			Route::middleware('web')
				->group(base_path('routes/web.php'));
		});
	}
}
