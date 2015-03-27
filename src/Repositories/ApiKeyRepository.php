<?php namespace Chrisbjr\ApiGuard\Repositories;

use Chrisbjr\ApiGuard\Models\ApiKey;

class ApiKeyRepository
{

    /**
     * @param $key
     * @return ApiKey|null
     */
    public static function getByKey($key)
    {
        $apiKey = ApiKey::where('key', '=', $key)
            ->first();

        if (empty($apiKey) || $apiKey->exists == false) {
            return null;
        }

        return $apiKey;
    }

}