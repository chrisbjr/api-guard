<?php namespace Chrisbjr\ApiGuard\Http\Middleware;

use Chrisbjr\ApiGuard\Repositories\ApiKeyRepository;
use Closure;
use Config;
use EllipseSynergie\ApiResponse\Laravel\Response;
use League\Fractal\Manager;

class ApiGuard
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $key = $request->header(Config::get('apiguard.keyName', 'X-Authorization'), null);

        $manager = new Manager;

        $response = new Response($manager);

        if (empty($key)) {
            return $response->errorUnauthorized();
        }

        $apiKey = ApiKeyRepository::getByKey($key);

        if (empty($apiKey)) {
            return $response->errorUnauthorized();
        }

        return $next($request);
    }

}
