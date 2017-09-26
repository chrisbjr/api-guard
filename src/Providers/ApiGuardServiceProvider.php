<?php

namespace Chrisbjr\ApiGuard\Providers;

use Chrisbjr\ApiGuard\Console\Commands\GenerateApiKey;
use Chrisbjr\ApiGuard\Http\Middleware\AuthenticateApiKey;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class ApiGuardServiceProvider extends ServiceProvider
{
    protected $middlewares = [
        'auth.apikey' => AuthenticateApiKey::class,
    ];

    /**
     * Bootstrap the application services.
     *
     * @param Router $router
     * @return void
     */
    public function boot(Router $router)
    {
        // Publish migrations
        $this->publishFiles();

        $this->defineMiddleware($router);
    }

    /** /
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            GenerateApiKey::class,
        ]);
    }

    private function defineMiddleware($router)
    {
        foreach ($this->middlewares as $name => $class) {
            if ( version_compare(app()->version(), '5.4.0') >= 0 ) {
                $router->aliasMiddleware($name, $class);
            } else {
                $router->middleware($name, $class);
            }
        }
    }

    private function publishFiles()
    {
        $this->publishes([
            __DIR__ . '/../../database/migrations/' => base_path('/database/migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/../../config/apiguard.php' => config_path('apiguard.php'),
        ], 'config');
    }
}
