<?php

namespace Chrisbjr\ApiGuard;

use Controller;
use Route;
use Request;
use Config;
use Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Input;
use EllipseSynergie\ApiResponse\Laravel\Response;

class ApiGuardController extends Controller
{

    protected $apiMethods;
    public $apiKey;
    public $response;

    public function __construct()
    {
        $this->beforeFilter(function () {

            // Let's instantiate the response class first
            $this->response = new Response(new \League\Fractal\Manager);

            // This is the $apiMethods declared in the controller
            $apiMethods = $this->getBeforeFilters()[0]['options']['apiMethods'];

            // Let's get the method
            Str::parseCallback(Route::currentRouteAction(), null);
            $routeArray = Str::parseCallback(Route::currentRouteAction(), null);

            if (last($routeArray) == null) {
                // There is no method?
                return $this->response->errorMethodNotAllowed();
            }

            $method = last($routeArray);

            // We should check if key authentication is enabled for this method
            $keyAuthentication = true;
            if (isset($apiMethods[$method]['keyAuthentication']) && $apiMethods[$method]['keyAuthentication'] === false) {
                $keyAuthentication = false;
            }

            if ($keyAuthentication === true) {

                $key = Request::header(Config::get('api-guard::keyName'));
                if (empty($key)) {
                    // Try getting the key from elsewhere
                    $key = Input::get(Config::get('api-guard::keyName'));
                }

                if (empty($key)) {
                    return $this->response->errorUnauthorized();
                }

                $this->apiKey = ApiKey::where('key', '=', $key)->first();

                if (!isset($this->apiKey->id)) {
                    return $this->response->errorUnauthorized();
                }

                // API key exists
                // Check level of API
                if (!empty($apiMethods[$method]['level'])) {
                    if ($this->apiKey->level < $apiMethods[$method]['level']) {
                        return $this->response->errorForbidden();
                    }
                }

                // Then check the limits of this method
                if (!empty($apiMethods[$method]['limit'])) {

                    if (Config::get('api-guard::logging') === false) {
                        Log::warning("[chrisbjr/ApiGuard] You specified a limit in the $method method but API logging needs to be enabled in the configuration for this to work.");
                    }

                    if (!$this->apiKey->ignore_limits) {
                        // Count the number of requests for this method using this api key
                        $apiLogCount = ApiLog::where('api_key_id', '=', $this->apiKey->id)
                            ->where('route', '=', Route::currentRouteAction())
                            ->where('method', '=', Request::getMethod())
                            ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-1 hour')))
                            ->where('created_at', '<=', date('Y-m-d H:i:s'))
                            ->count();

                        if ($apiLogCount >= $apiMethods[$method]['limit']) {
                            return $this->response->errorUnwillingToProcess('You have reached the limit for using this API.');
                        }
                    }
                }

                if (Config::get('api-guard::logging')) {
                    // Log this API request
                    $apiLog = new ApiLog;
                    $apiLog->api_key_id = $this->apiKey->id;
                    $apiLog->route = Route::currentRouteAction();
                    $apiLog->method = Request::getMethod();
                    $apiLog->params = http_build_query(Input::all());
                    $apiLog->ip_address = Request::getClientIp();
                    $apiLog->save();
                }

            }


        }, ['apiMethods' => $this->apiMethods]);
    }

}