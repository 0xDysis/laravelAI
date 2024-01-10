<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MessageService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MessageService::class, function ($app) {
            return new MessageService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
