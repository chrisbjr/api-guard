<?php

namespace Chrisbjr\ApiGuard\Http\Controllers;

use Input;
use Config;
use Exception;
use League\Fractal\Manager;
use Illuminate\Routing\Controller;
use EllipseSynergie\ApiResponse\Laravel\Response;

class ApiGuardController extends Controller
{

  public $apiKey = null;
  public $apiLog = null;

  public function __construct()
  {

    $serializedApiMethods = serialize($this->apiMethods);

    // Let's instantiate the response class first
    $manager = new Manager;

    // Launch middleware
    $this->middleware('apiguard:'.$serializedApiMethods);

    $manager->parseIncludes(Input::get(Config::get('apiguard.includeKeyword', 'include'), 'include'));

    $this->response = new Response($manager);
  }

}
