<?php

namespace Chrisbjr\ApiGuard\Providers;

use Chrisbjr\ApiGuard\ApiGuardAuth;
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
            __DIR__ . '/../../database/migrations/' => base_path('/database/migrations'),
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
        $this->registerApiGuardAuth();
    }

    /**
     * Register the bindings for the main JWTAuth class.
     */
    protected function registerApiGuardAuth()
    {
        $this->app->singleton('Chrisbjr\ApiGuard\ApiGuardAuth', function () {
            return new ApiGuardAuth($this->getConfigInstance('providers.auth'));
        });
    }

    /**
     * Helper to get the config values.
     *
     * @param  string $key
     * @param  string $default
     *
     * @return mixed
     */
    protected function config($key, $default = null)
    {
        return config("apiguard.$key", $default);
    }

    /**
     * Get an instantiable configuration instance.
     *
     * @param  string $key
     *
     * @return mixed
     */
    protected function getConfigInstance($key)
    {
        $instance = $this->config($key);
        if (is_string($instance)) {
            return $this->app->make($instance);
        }

        return $instance;
    }

}
