<?php

namespace Chrisbjr\ApiGuard\Providers;

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

        $this->commands([
            'Chrisbjr\ApiGuard\Console\Commands\GenerateApiKeyCommand',
            'Chrisbjr\ApiGuard\Console\Commands\DeleteApiKeyCommand',
        ]);

        // Publish your migrations
        $this->publishes([
            __DIR__ . '/../../database/migrations/' => base_path('/database/migrations')
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/../../config/apiguard.php' => config_path('apiguard.php'),
        ], 'config');
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
