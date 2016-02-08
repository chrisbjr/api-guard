<?php

namespace Chrisbjr\ApiGuard\Http\Middleware;

use App;
use Log;
use Route;
use Config;
use Closure;
use Illuminate\Support\Str;
use League\Fractal\Manager;
use Illuminate\Support\Facades\Input;
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
    public function handle($request, Closure $next, $serializedApiMethods=null)
    {

      // Unserialize parameters
      if($serializedApiMethods !== null)
      {
          $apiMethods = unserialize($serializedApiMethods);
      }
      else
      {
          $apiMethods = [];
      }

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

      // Then check the limits of this method
      if (!empty($apiMethods[$method]['limits'])) {
          if (Config::get('apiguard.logging', true) === false) {
              Log::warning("[ApiGuard] You specified a limit in the $method method but API logging needs to be enabled in the configuration for this to work.");
          }
          $limits = $apiMethods[$method]['limits'];
          // We get key level limits first
          if ($this->apiKey != null && ! empty($limits['key'])) {
              $keyLimit = ( ! empty($limits['key']['limit'])) ? $limits['key']['limit'] : 0;
              if ($keyLimit == 0 || is_integer($keyLimit) == false) {
                  Log::warning("[ApiGuard] You defined a key limit to the " . Route::currentRouteAction() . " route but you did not set a valid number for the limit variable.");
              } else {
                  if ( ! $this->apiKey->ignore_limits) {
                      // This means the apikey is not ignoring the limits
                      $keyIncrement = ( ! empty($limits['key']['increment'])) ? $limits['key']['increment'] : Config::get('apiguard.keyLimitIncrement', '1 hour');
                      $keyIncrementTime = strtotime('-' . $keyIncrement);
                      if ($keyIncrementTime == false) {
                          Log::warning("[ApiGuard] You have specified an invalid key increment time. This value can be any value accepted by PHP's strtotime() method");
                      } else {
                          // Count the number of requests for this method using this api key
                          $apiLogCount = $apiLog->countApiKeyRequests($this->apiKey->id, Route::currentRouteAction(), $request->getMethod(), $keyIncrementTime);
                          if ($apiLogCount >= $keyLimit) {
                              Log::warning("[ApiGuard] The API key ID#{$this->apiKey->id} has reached the limit of {$keyLimit} in the following route: " . Route::currentRouteAction());
                              return $response->setStatusCode(429)->withError('You have reached the limit for using this API.', 'GEN-TOO-MANY-REQUESTS');
                          }
                      }
                  }
              }
          }

          // Then the overall method limits
          if ( ! empty($limits['method'])) {
              $methodLimit = ( ! empty($limits['method']['limit'])) ? $limits['method']['limit'] : 0;
              if ($methodLimit == 0 || is_integer($methodLimit) == false) {
                  Log::warning("[ApiGuard] You defined a method limit to the " . Route::currentRouteAction() . " route but you did not set a valid number for the limit variable.");
              } else {
                  if ($this->apiKey != null && $this->apiKey->ignore_limits == true) {
                      // then we skip this
                  } else {
                      $methodIncrement = ( ! empty($limits['method']['increment'])) ? $limits['method']['increment'] : Config::get('apiguard.keyLimitIncrement', '1 hour');
                      $methodIncrementTime = strtotime('-' . $methodIncrement);
                      if ($methodIncrementTime == false) {
                          Log::warning("[ApiGuard] You have specified an invalid method increment time. This value can be any value accepted by PHP's strtotime() method");
                      } else {
                          // Count the number of requests for this method
                          $apiLogCount = $apiLog->countMethodRequests(Route::currentRouteAction(), $request->getMethod(), $methodIncrementTime);
                          if ($apiLogCount >= $methodLimit) {
                              Log::warning("[ApiGuard] The API has reached the method limit of {$methodLimit} in the following route: " . Route::currentRouteAction());
                              return $response->setStatusCode(429)->withError('The limit for using this API method has been reached', 'GEN-TOO-MANY-REQUESTS');
                          }
                      }
                  }
              }
          }
      }

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

        // login User
        $headers = apache_request_headers();
        //$api_key = $headers[Config::get('apiguard.keyName', 'X-Authorization')];
        
        if (empty($headers[Config::get('apiguard.keyName', 'X-Authorization')])) {
            $api_key = null;
        } else {
            $api_key = $headers[Config::get('apiguard.keyName', 'X-Authorization')];
        }
        
        if(!empty($api_key)) {

        $user_id = App::make(Config::get('apiguard.model', 'Chrisbjr\ApiGuard\Models\ApiKey'))->where('key', $api_key)
            ->pluck('user_id');

        if($user_id !== 0)
            Auth::loginUsingId($user_id);

        return $next($request);
        }
    }
}
