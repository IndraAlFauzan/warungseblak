<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register XenditGatewayClient with GuzzleHttp\Client dependency
        $this->app->singleton(
            \App\Services\XenditGatewayClient::class,
            fn() =>
            new \App\Services\XenditGatewayClient(new \GuzzleHttp\Client())
        );

        // Register other services as singletons for better performance
        $this->app->singleton(\App\Services\PaymentService::class);
        $this->app->singleton(\App\Services\TransactionService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
