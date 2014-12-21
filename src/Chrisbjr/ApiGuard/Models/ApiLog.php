<?php namespace Chrisbjr\ApiGuard\Models;

use Eloquent;

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
        return $this->hasOne('ApiKey');
    }

}