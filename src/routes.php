<?php

if (Config::get('api-guard::generateApiKeyRoute')) {
    Route::post('apiguard/api_key', 'Chrisbjr\ApiGuard\ApiKeyController@create');
}