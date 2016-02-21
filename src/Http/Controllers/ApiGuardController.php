<?php

namespace Chrisbjr\ApiGuard\Http\Controllers;

use ApiGuardAuth;
use Chrisbjr\ApiGuard\Builders\ApiResponseBuilder;
use Illuminate\Routing\Controller;
use EllipseSynergie\ApiResponse\Laravel\Response;

class ApiGuardController extends Controller
{

    /**
     * @var Response
     */
    public $response;

    /**
     * The authenticated user
     *
     * @var
     */
    public $user;

    /**
     * @var array
     */
    protected $apiMethods;

    public function __construct()
    {
        $serializedApiMethods = serialize($this->apiMethods);

        // Launch middleware
        $this->middleware('apiguard:' . $serializedApiMethods);

        // Attempt to get an authenticated user.
        $this->user = ApiGuardAuth::getUser();

        $this->response = ApiResponseBuilder::build();
    }

}