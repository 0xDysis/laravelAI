<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MyOpenAIService;
use App\Services\DatabaseExportService;
use App\Services\FileDownloadService;
use App\Services\MessageService;
use App\Services\RunService;
use App\Services\ThreadService;
use App\Services\AssistantService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register MyOpenAIService
        $this->app->singleton(MyOpenAIService::class, function ($app) {
            return new MyOpenAIService();
        });

        // MessageService now depends on MyOpenAIService
        $this->app->singleton(MessageService::class, function ($app) {
            return new MessageService($app->make(MyOpenAIService::class));
        });

        // RunService now depends on MyOpenAIService
        $this->app->singleton(RunService::class, function ($app) {
            return new RunService($app->make(MyOpenAIService::class));
        });

        // FileDownloadService now depends on MyOpenAIService
        $this->app->singleton(FileDownloadService::class, function ($app) {
            return new FileDownloadService($app->make(MyOpenAIService::class));
        });

        // DatabaseExportService now depends on MyOpenAIService
        $this->app->singleton(DatabaseExportService::class, function ($app) {
            return new DatabaseExportService($app->make(MyOpenAIService::class));
        });

        // ThreadService now depends on MyOpenAIService
        $this->app->singleton(ThreadService::class, function ($app) {
            return new ThreadService($app->make(MyOpenAIService::class));
        });

        // AssistantService now depends on MyOpenAIService
        $this->app->singleton(AssistantService::class, function ($app) {
            return new AssistantService($app->make(MyOpenAIService::class));
        });
    }

    public function boot(): void
    {
        //
    }
}

