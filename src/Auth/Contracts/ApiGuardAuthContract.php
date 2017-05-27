<?php

namespace Chrisbjr\ApiGuard\Auth\Contracts;

use Illuminate\Http\Request;

interface ApiGuardAuthContract
{
    /**
     * A method that will be triggered to indicate that a particular user/object has logged in.
     *
     * @param Request $request
     * @param $user
     * @return void
     */
    public function authenticate(Request $request, $user);
}
