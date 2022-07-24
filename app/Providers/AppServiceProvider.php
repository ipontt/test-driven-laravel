<?php

namespace App\Providers;

use App\Billing\Concerns\PaymentGateway;
use App\Billing\Stripe\StripePaymentGateway;
use App\HashidsTicketCodeGenerator;
use App\TicketCodeGenerator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            abstract: StripePaymentGateway::class,
            concrete: fn () => new StripePaymentGateway(config('services.stripe.secret')),
        );

        $this->app->bind(
            abstract: PaymentGateway::class,
            concrete: StripePaymentGateway::class,
        );

        $this->app->bind(
            abstract: TicketCodeGenerator::class,
            concrete: HashidsTicketCodeGenerator::class,
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
