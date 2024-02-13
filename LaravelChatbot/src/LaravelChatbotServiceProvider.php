<?php
use Illuminate\Support\ServiceProvider;

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

        // Publish migrations
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/database/migrations/' => database_path('migrations'),
            ], 'laravelchatbot-migrations');
        }
    }
    
    public function register()
    {
        // Register controllers as services if needed (usually not necessary)
        // Register your services in the container for dependency injection
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

        // You may also bind interfaces to implementations here
    }
}

// Remember to replace `AssistantService::class` and other service classes
// with the actual full namespace paths to your service classes.

