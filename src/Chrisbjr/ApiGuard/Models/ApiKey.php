<?php namespace Chrisbjr\ApiGuard\Models;

use Carbon\Carbon;
use Config;
use Eloquent;

/**
 * ApiKey Eloquent Model
 *
 * @property int id
 * @property int user_id
 * @property string key
 * @property int level
 * @property int ignore_limits
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class ApiKey extends Eloquent
{
    protected $table = 'api_keys';

    public function user()
    {
        return $this->belongsTo(Config::get('auth.model'));
    }

    /**
     * A sure method to generate a unique API key
     *
     * @return string
     */
    public function generateKey()
    {
        do {
            $salt = sha1(time() . mt_rand());
            $newKey = substr($salt, 0, 40);
        } // Already in the DB? Fail. Try again
        while ($this->keyExists($newKey));

        return $newKey;
    }

    /**
     * Checks whether a key exists in the database or not
     *
     * @param $key
     * @return bool
     */
    private function keyExists($key)
    {
        $apiKeyCount = self::where('key', '=', $key)->limit(1)->count();

        if ($apiKeyCount > 0) return true;

        return false;
    }

    public function findApiKey($key) {
        return self::where('key', '=', $key)->first();
    }

}