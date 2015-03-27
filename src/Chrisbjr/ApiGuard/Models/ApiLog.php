<?php namespace Chrisbjr\ApiGuard\Models;

use Eloquent;
use Config;

/**
 * @property int api_key_id
 * @property string route
 * @property string method
 * @property string params
 * @property string ip_address
 */
class ApiLog extends Eloquent
{

    protected $table = 'api_logs';

    /**
     * @return ApiKey
     */
    public function apiKey()
    {
        return $this->hasOne(Config::get('api-guard::model'));
    }

    public function countApiKeyRequests($apiKeyId, $routeAction, $method, $keyIncrementTime)
    {
        return self::where('api_key_id', '=', $apiKeyId)
            ->where('route', '=', $routeAction)
            ->where('method', '=', $method)
            ->where('created_at', '>=', date('Y-m-d H:i:s', $keyIncrementTime))
            ->where('created_at', '<=', date('Y-m-d H:i:s'))
            ->count();
    }

    public function countMethodRequests($routeAction, $method, $keyIncrementTime)
    {
        return self::where('route', '=', $routeAction)
            ->where('method', '=', $method)
            ->where('created_at', '>=', date('Y-m-d H:i:s', $keyIncrementTime))
            ->where('created_at', '<=', date('Y-m-d H:i:s'))
            ->count();
    }

}