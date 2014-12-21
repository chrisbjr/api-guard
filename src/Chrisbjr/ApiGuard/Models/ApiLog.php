<?php
namespace Chrisbjr\ApiGuard;
/**
 * Class ApiKey
 */
class ApiLog extends \Eloquent
{
    protected $table = 'api_logs';

    public function user()
    {
        return $this->hasOne('ApiKey');
    }

}