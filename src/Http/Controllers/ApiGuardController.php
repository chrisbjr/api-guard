<?php

namespace Chrisbjr\ApiGuard\Http\Controllers;

use Illuminate\Routing\Controller;

class ApiGuardController extends Controller
{

    public function __construct()
    {
        $this->middleware('Chrisbjr\ApiGuard\Http\Middleware\ApiGuard');
    }

}