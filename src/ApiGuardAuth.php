<?php

namespace Chrisbjr\ApiGuard;

use App;
use Chrisbjr\ApiGuard\Contracts\Providers\Auth;
use Chrisbjr\ApiGuard\Repositories\ApiKeyRepository;

class ApiGuardAuth
{

    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Authenticate a user via the API key.
     *
     * @param ApiKeyRepository $apiKey
     * @return bool|mixed
     */
    public function authenticate(ApiKeyRepository $apiKey)
    {
        if ( ! $this->auth->byId($apiKey->user_id)) {
            return false;
        }

        return $this->getUser();
    }

    /**
     * Determines if we have an authenticated user
     *
     * @return bool
     */
    public function isAuthenticated()
    {
        $user = $this->getUser();

        if ( ! isset($user)) {
            return false;
        }

        return true;
    }

    /**
     * Get the authenticated user.
     */
    public function getUser()
    {
        return $this->auth->user();
    }

}