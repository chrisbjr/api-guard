<?php

if (Config::get('api-guard::generateApiKeyRoute')) {
    Route::post('api/api-key', 'Chrisbjr\ApiGuard\Controllers\ApiKeyController@create');
}