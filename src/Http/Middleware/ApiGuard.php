<?php

namespace Chrisbjr\ApiGuard\Http\Middleware;

use App;
use Log;
use Route;
use Input;
use Config;
use Closure;
use Illuminate\Support\Str;
use League\Fractal\Manager;
use EllipseSynergie\ApiResponse\Laravel\Response;
use Chrisbjr\ApiGuard\Repositories\ApiKeyRepository;
use Chrisbjr\ApiGuard\Repositories\ApiLogRepository;

class ApiGuard
{

    public $apiKey = null;
    public $apiLog = null;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $serializedApiMethods)
    {

      // Unserialize parameters
      $apiMethods = unserialize($serializedApiMethods);

      // Let's instantiate the response class first
      $manager = new Manager;

      $manager->parseIncludes(Input::get(Config::get('apiguard.includeKeyword', 'include'), 'include'));

      $response = new Response($manager);

      // This is the actual request object used
      $request = $request;

      // Let's get the method
      Str::parseCallback(Route::currentRouteAction(), null);

      $routeArray = Str::parseCallback(Route::currentRouteAction(), null);

      if (last($routeArray) == null) {
          // There is no method?
          return $response->errorMethodNotAllowed();
      }

      $method = last($routeArray);

      // We should check if key authentication is enabled for this method
      $keyAuthentication = true;

      if (isset($apiMethods[$method]['keyAuthentication']) && $apiMethods[$method]['keyAuthentication'] === false) {
          $keyAuthentication = false;
      }

      if ($keyAuthentication === true) {

          $key = $request->header(Config::get('apiguard.keyName', 'X-Authorization'));

          if (empty($key)) {
              // Try getting the key from elsewhere
              $key = Input::get(Config::get('apiguard.keyName', 'X-Authorization'));
          }

          if (empty($key)) {
              // It's still empty!
              return $response->errorUnauthorized();
          }

          $apiKeyModel = App::make(Config::get('apiguard.model', 'Chrisbjr\ApiGuard\Models\ApiKey'));

          if ( ! $apiKeyModel instanceof ApiKeyRepository) {
              Log::error('[ApiGuard] You ApiKey model should be an instance of ApiKeyRepository.');
              $exception = new Exception("You ApiKey model should be an instance of ApiKeyRepository.");
              throw($exception);
          }

          $this->apiKey = $apiKeyModel->getByKey($key);

          if (empty($this->apiKey)) {
              return $response->errorUnauthorized();
          }

          // API key exists
          // Check level of API
          if ( ! empty($apiMethods[$method]['level'])) {
              if ($this->apiKey->level < $apiMethods[$method]['level']) {
                  return $response->errorForbidden();
              }
          }
      }

      $apiLog = App::make(Config::get('apiguard.apiLogModel', 'Chrisbjr\ApiGuard\Models\ApiLog'));

      // End of cheking limits
      if (Config::get('apiguard.logging', true)) {
          // Default to log requests from this action
          $logged = true;

          if (isset($apiMethods[$method]['logged']) && $apiMethods[$method]['logged'] === false) {
              $logged = false;
          }

          if ($logged) {
              // Log this API request
              $this->apiLog = App::make(Config::get('apiguard.apiLogModel', 'Chrisbjr\ApiGuard\Models\ApiLog'));

              if (isset($this->apiKey)) {
                  $this->apiLog->api_key_id = $this->apiKey->id;
              }

              $this->apiLog->route      = Route::currentRouteAction();
              $this->apiLog->method     = $request->getMethod();
              $this->apiLog->params     = http_build_query(Input::all());
              $this->apiLog->ip_address = $request->getClientIp();
              $this->apiLog->save();

          }
      }

        return $next($request);
    }
}
