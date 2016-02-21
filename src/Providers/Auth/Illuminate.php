<?php

namespace Chrisbjr\ApiGuard\Providers\Auth;

use Chrisbjr\ApiGuard\Contracts\Providers\Auth;
use Illuminate\Contracts\Auth\Guard as GuardContract;

class Illuminate implements Auth
{

    /**
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * @param GuardContract $auth
     */
    public function __construct(GuardContract $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Check a user's credentials.
     *
     * @param  array $credentials
     *
     * @return bool
     */
    public function byCredentials(array $credentials)
    {
        return $this->auth->once($credentials);
    }

    /**
     * Authenticate a user via the id.
     *
     * @param  mixed $id
     *
     * @return bool
     */
    public function byId($id)
    {
        return $this->auth->onceUsingId($id);
    }

    /**
     * Get the currently authenticated user.
     *
     * @return mixed
     */
    public function user()
    {
        return $this->auth->user();
    }
}