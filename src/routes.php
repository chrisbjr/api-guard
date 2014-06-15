<?php

if(Config::get('api-guard::registerApi')) {
    Route::post('apiguard/generate', 'Chrisbjr\ApiGuard\ApiGuardApiController@postGenerate');
}
