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
        return $this->hasOne('User');
    }

    public function generateKey()
    {
        do {
            $salt = do_hash(time() . mt_rand());
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