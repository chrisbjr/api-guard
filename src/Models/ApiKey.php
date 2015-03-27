<?php

namespace Chrisbjr\ApiGuard\Models;
use Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int user_id
 * @property string key
 */
class ApiKey extends Eloquent
{

    protected $table = 'api_keys';

    use SoftDeletes;

    protected $dates = ['deleted_at'];

}