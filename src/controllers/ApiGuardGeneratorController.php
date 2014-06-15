<?php
/**
 * Created by PhpStorm.
 * User: chrisbjr
 * Date: 6/15/14
 * Time: 1:22 PM
 */

namespace Chrisbjr\ApiGuard;

use Input;


class ApiGuardGeneratorController extends ApiGuardController
{
    protected $apiMethods = [
        'postRegister' => [
            'keyAuthentication' => false
        ]
    ];

    function postGenerate()
    {
        $apiKey = new ApiKey;
        $apiKey->key = $apiKey->generateKey();
        $apiKey->user_id = Input::get('user_id', 0);
        $apiKey->level = Input::get('level', 10);
        $apiKey->ignore_limits = Input::get('ignore_limits', 1);

        if ($apiKey->save()) {
            return $this->response($apiKey->toArray(), 201);
        } else {
            return $this->response(null, 400, 'Failed to create an API key.');
        }
    }

}