<?php

namespace Chrisbjr\ApiGuard\Builders;

use EllipseSynergie\ApiResponse\Laravel\Response;
use League\Fractal\Manager;
use Request;

class ApiResponseBuilder
{

    /**
     * @param null $includes
     * @return Response
     */
    public static function build($includes = null)
    {
        // Let's instantiate the response class first
        $manager = new Manager;

        if (is_null($includes)) {
            $includeKeyword = config('apiguard.includeKeyword', 'include');

            $manager->parseIncludes(Request::get($includeKeyword, 'include'));
        }

        return new Response($manager);
    }

}