<?php
/**
 * Created by PhpStorm.
 * User: chrisbjr
 * Date: 6/26/14
 * Time: 7:52 PM
 */

namespace Chrisbjr\ApiGuard;

use Chrisbjr\ApiGuard\Transformers\ApiKeyTransformer;

class ApiKeyController extends ApiGuardController
{

    protected $apiMethods = [
        'create' => [
            'keyAuthentication' => false
        ]
    ];

    public function create()
    {
        $apiKey = new ApiKey;
        $apiKey->key = $apiKey->generateKey();
        $apiKey->user_id = \Input::json('user_id', 0);
        $apiKey->level = \Input::json('level', 10);
        $apiKey->ignore_limits = \Input::json('ignore_limits', 1);

        if ($apiKey->save() === false) {
            return $this->response->errorInternalError("Failed to save API key to the database.");
        }

        $this->response->setStatusCode(201);

        return $this->response->withItem($apiKey, new ApiKeyTransformer);
    }

} 