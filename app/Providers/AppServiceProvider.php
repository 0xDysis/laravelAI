<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\DatabaseExportService;
use App\Services\FileDownloadService;
use App\Services\MessageService;
use App\Services\PHPScriptRunnerService;
use App\Services\RunService;
use App\Services\SessionValidationService;
use App\Services\ThreadService;
use App\Services\AssistantService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // PHPScriptRunnerService doesn't seem to have dependencies, so it can be new-ed up directly.
        $this->app->singleton(PHPScriptRunnerService::class, function ($app) {
            return new PHPScriptRunnerService();
        });

        // Assuming MessageService depends on PHPScriptRunnerService
        $this->app->singleton(MessageService::class, function ($app) {
            return new MessageService($app->make(PHPScriptRunnerService::class));
        });

        // Assuming RunService depends on PHPScriptRunnerService
        $this->app->singleton(RunService::class, function ($app) {
            return new RunService($app->make(PHPScriptRunnerService::class));
        });

        // Assuming FileDownloadService depends on PHPScriptRunnerService
        $this->app->singleton(FileDownloadService::class, function ($app) {
            return new FileDownloadService($app->make(PHPScriptRunnerService::class));
        });

        // Assuming DatabaseExportService depends on PHPScriptRunnerService
        $this->app->singleton(DatabaseExportService::class, function ($app) {
            return new DatabaseExportService($app->make(PHPScriptRunnerService::class));
        });
        $this->app->singleton(ThreadService::class, function ($app) {
            return new ThreadService($app->make(PHPScriptRunnerService::class));
        });

        // Register AssistantService
        $this->app->singleton(AssistantService::class, function ($app) {
            return new AssistantService($app->make(PHPScriptRunnerService::class));
        });

        // SessionValidationService doesn't seem to have dependencies, so it can be new-ed up directly.
        $this->app->singleton(SessionValidationService::class, function () {
            return new SessionValidationService();
        });

        // Register other services similarly...
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

