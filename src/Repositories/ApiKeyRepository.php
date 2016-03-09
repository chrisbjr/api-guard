<?php

namespace Chrisbjr\ApiGuard\Repositories;

use App;
use Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ApiKeyRepository
 *
 * @package Chrisbjr\ApiGuard\Repositories
 */
abstract class ApiKeyRepository extends Eloquent
{

    use SoftDeletes;

    protected $table = 'api_keys';

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'user_id',
        'key',
        'level',
        'ignore_limits',
    ];

    /**
     * @param $key
     * @return ApiKeyRepository
     */
    public function getByKey($key)
    {
        $apiKey = self::where('key', '=', $key)
            ->first();

        if (empty($apiKey) || $apiKey->exists == false) {
            return null;
        }

        return $apiKey;
    }

    /**
     * A sure method to generate a unique API key
     *
     * @return string
     */
    public static function generateKey()
    {
        do {
            $salt = sha1(time() . mt_rand());
            $newKey = substr($salt, 0, 40);
        } // Already in the DB? Fail. Try again
        while (self::keyExists($newKey));

        return $newKey;
    }

    /**
     * Make an ApiKey
     *
     * @param null $userId
     * @param int $level
     * @param bool $ignoreLimits
     * @return static
     */
    public static function make($userId = null, $level = 10, $ignoreLimits = false)
    {
        return self::create([
            'user_id'       => $userId,
            'key'           => self::generateKey(),
            'level'         => $level,
            'ignore_limits' => $ignoreLimits,
        ]);
    }

    /**
     * Checks whether a key exists in the database or not
     *
     * @param $key
     * @return bool
     */
    private static function keyExists($key)
    {
        $apiKeyCount = self::where('key', '=', $key)->limit(1)->count();

        if ($apiKeyCount > 0) return true;

        return false;
    }

}