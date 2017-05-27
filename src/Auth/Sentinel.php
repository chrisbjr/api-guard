<?php

namespace Chrisbjr\ApiGuard\Auth;

use Chrisbjr\ApiGuard\Auth\Contracts\ApiGuardAuthContract;
use Illuminate\Http\Request;
use Sentinel as SentinelAuth;

/**
 * A sample class that shows what you can do with this interface. Here, we use
 * this class to trigger the "login" method provided by Sentinel. The "login"
 * method from Sentinel logs that the user has logged in and populates the
 * "last_login" field in the database.
 *
 * @package Chrisbjr\ApiGuard\Auth
 */
class Sentinel implements ApiGuardAuthContract
{
    /**
     * @param Request $request
     * @param $user
     */
    public function authenticate(Request $request, $user)
    {
        SentinelAuth::login($user);
    }
}
