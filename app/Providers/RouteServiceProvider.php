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
	public const HOME = '/home';

	public function boot(): void
	{
		Route::bind('user_concert', fn ($id) => Auth::authenticate()->concerts()->findOrFail($id));
		Route::bind('published_concert', fn ($id) => Concert::published()->findOrFail($id));
		Route::bind('user_published_concert', fn ($id) => Auth::authenticate()->concerts()->published()->findOrFail($id));

		$this->configureRateLimiting();

		$this->routes(function () {
			Route::middleware('api')
				->prefix('api')
				->group(base_path('routes/api.php'));

			Route::middleware('web')
				->group(base_path('routes/web.php'));
		});
	}

	protected function configureRateLimiting(): void
	{
		RateLimiter::for('api', function (Request $request) {
			return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
		});
	}
}
