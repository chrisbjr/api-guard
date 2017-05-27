<?php

namespace Chrisbjr\ApiGuard\Http\Controllers;

use EllipseSynergie\ApiResponse\Laravel\Response;
use Illuminate\Routing\Controller;
use League\Fractal\Manager;

class ApiGuardController extends Controller
{
    /**
     * @var Response
     */
    protected $response;

    public function __construct()
    {
        $fractal = new Manager();

        if (isset($_GET['include'])) {
            $fractal->parseIncludes($_GET['include']);
        }

        $this->response = new Response($fractal);
    }
}
