<?php

namespace LaravelAI\LaravelChatbot;

use Illuminate\Support\ServiceProvider;
use LaravelAI\LaravelChatbot\Services\AssistantService;
use LaravelAI\LaravelChatbot\Services\DatabaseExportService;
use LaravelAI\LaravelChatbot\Services\FileDownloadService;
use LaravelAI\LaravelChatbot\Services\MessageService;
use LaravelAI\LaravelChatbot\Services\MyOpenAIService;
use LaravelAI\LaravelChatbot\Services\RunService;
use LaravelAI\LaravelChatbot\Services\ThreadService;

class LaravelChatbotServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Load the routes
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        
        // Load views if your package has views
        $this->loadViewsFrom(__DIR__.'/resources/views', 'laravelchatbot');

        // Publish models, config, views, and other resources
        $this->publishes([
            __DIR__.'/Models' => app_path('Models/LaravelChatbot'),
            __DIR__.'/config/laravelchatbot.php' => config_path('laravelchatbot.php'),
        ], 'laravelchatbot');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/database/migrations/' => database_path('migrations'),
            ], 'laravelchatbot-migrations');
        }
    }
    
    public function register()
    {
        
        $this->app->singleton(AssistantService::class, function ($app) {
            return new AssistantService();
        });
        $this->app->singleton(DatabaseExportService::class, function ($app) {
            return new DatabaseExportService();
        });
        $this->app->singleton(FileDownloadService::class, function ($app) {
            return new FileDownloadService();
        });
        $this->app->singleton(MessageService::class, function ($app) {
            return new MessageService();
        });
        $this->app->singleton(MyOpenAIService::class, function ($app) {
            return new MyOpenAIService();
        });
        $this->app->singleton(RunService::class, function ($app) {
            return new RunService();
        });
        $this->app->singleton(ThreadService::class, function ($app) {
            return new ThreadService();
        });

       
    }
}
