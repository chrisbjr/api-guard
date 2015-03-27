<?php namespace Chrisbjr\ApiGuard\Providers;

use Illuminate\Support\ServiceProvider;

class ApiGuardServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->register('EllipseSynergie\ApiResponse\Laravel\ResponseServiceProvider');

        // Publish your migrations
        $this->publishes([
            __DIR__ . '/../../database/migrations/' => base_path('/database/migrations')
        ], 'migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

}
