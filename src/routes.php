<?php

if(Config::get('api-guard::generateApiRoute')) {
    Route::post('apiguard/generate', 'Chrisbjr\ApiGuard\ApiGuardGeneratorController@postGenerate');
}
