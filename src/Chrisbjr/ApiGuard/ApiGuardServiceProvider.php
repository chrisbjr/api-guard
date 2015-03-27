<?php namespace Chrisbjr\ApiGuard;

use Illuminate\Support\ServiceProvider;

class ApiGuardServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('chrisbjr/api-guard');

        $this->app->register('EllipseSynergie\ApiResponse\Laravel\ResponseServiceProvider');

        $this->commands([
            'Chrisbjr\ApiGuard\Commands\GenerateApiKeyCommand',
            'Chrisbjr\ApiGuard\Commands\DeleteApiKeyCommand',
        ]);

        require_once __DIR__ . '/../../routes.php';
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }

}
