<?php
namespace Chrisbjr\ApiGuard;

/**
 * Class ApiKey
 */
class ApiKey extends \Eloquent
{
    protected $table = 'api_keys';

    public function user()
    {
        return $this->belongsTo(\Config::get('auth.model'));
    }

    public function generateKey()
    {
        do {
            $salt = sha1(time() . mt_rand());
            $newKey = substr($salt, 0, 40);
        } // Already in the DB? Fail. Try again
        while ($this->keyExists($newKey));

        return $newKey;
    }

    private function keyExists($key)
    {
        $apiKeyCount = ApiKey::where('key', '=', $key)->limit(1)->count();

        if ($apiKeyCount > 0) return true;

        return false;
    }
}