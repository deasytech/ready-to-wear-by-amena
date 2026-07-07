<?php

namespace App\Providers;

use App\Services\CartService;
use App\Services\Payments\PaymentGatewayInterface;
use App\Services\Payments\PaystackGateway;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, PaystackGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(function (Login $event) {
            app(CartService::class)->mergeGuestCartIntoUser($event->user);
        });
    }
}
