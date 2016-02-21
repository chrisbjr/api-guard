<?php

namespace Chrisbjr\ApiGuard\Facades;

use Illuminate\Support\Facades\Facade;

class ApiGuardAuth extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Chrisbjr\ApiGuard\ApiGuardAuth';
    }

}