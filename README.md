ApiGuard
========

[![Join the chat at https://gitter.im/chrisbjr/api-guard](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/chrisbjr/api-guard?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

A simple way of authenticating your APIs with API keys using Laravel. This package uses the following libraries:

- philsturgeon's [Fractal](https://github.com/thephpleague/fractal)
- maximebeaudoin's [api-response](https://github.com/ellipsesynergie/api-response)

The concept for managing API keys is also taken from Phil Sturgeon's [codeigniter-restserver](https://github.com/philsturgeon/codeigniter-restserver).
I've been looking for an equivalent for Laravel but did not find any so this is an implementation for that.

## Quick start

### Laravel 4.2.x

In the `require` key of `composer.json` file add the following

    "chrisbjr/api-guard": "1.0.*"

Run the Composer update comand

    $ composer update

In your `config/app.php` add `'Chrisbjr\ApiGuard\ApiGuardServiceProvider'` to the end of the `providers` array

```php
'providers' => array(

    'Illuminate\Foundation\Providers\ArtisanServiceProvider',
    'Illuminate\Auth\AuthServiceProvider',
    ...
    'Chrisbjr\ApiGuard\ApiGuardServiceProvider',
),
```

Now generate the api-guard migration (make sure you have your database configuration set up correctly):

    $ php artisan migrate --package="chrisbjr/api-guard"

It will setup two tables - api_keys and api_logs.

### Generating your first API key

Once you're done with the required setup, you can now generate your first API key.

Run the following command to generate an API key:

`php artisan api-key:generate`

Generally, you will want to generate API keys for each user in your application. The `api_keys` table has a `user_id` field which you can populate for your users.

To generate an API key that is linked to a user, you can do the following:

`php artisan api-key:generate --user-id=1`

## Usage

Basic usage of ApiGuard is to create a controller and extend that class to use the `ApiGuardController`.

```php
<?php

use Chrisbjr\ApiGuard\ApiGuardController;

class BooksController extends ApiGuardController
{

    public function all()
    {
        $books = Book::all();

        return $this->response->withCollection($books, new BookTransformer);
    }
    
    public function show($id)
    {
        try {
            $book = Book::findOrFail($id);
            
            return $this->response->withItem($book, new BookTransformer);
        } catch (ModelNotFoundException $e) {
            return $this->response->errorNotFound();
        }
    }

}
```

You should be able to use the api-response object by using `$this->response`. More examples can be found on the Github page: [https://github.com/ellipsesynergie/api-response](https://github.com/ellipsesynergie/api-response).

You can access the above controller by creating a basic route in your `app/routes.php`:

```php
Route::get('api/v1/books', 'BooksController@all');
Route::get('api/v1/books/{id}', 'BooksController@show');
```

You will need to use your API key and put it in the header to access it. By default, the header value is using the `X-Authorization` parameter. You can change this in the config file.

Try calling this route using `curl`

    curl --header "X-Authorization: 2ed9d72e5596800bf805ca1c735e446df72019ef" http://localhost:8000/api/v1/books

You should get the following response:

```javascript
{
    "data": {
        "id": 1,
        "title": "The Great Adventures of Chris",
        "created_at": {
            "date": "2014-03-25 18:54:18",
            "timezone_type": 3,
            "timezone": "UTC"
        },
        "updated_at": {
            "date": "2014-03-25 18:54:18",
            "timezone_type": 3,
            "timezone": "UTC"
        },
        "deleted_at": null
    }
}
```

## API Options

There are various options that can be specified for each method in your controller. These options can be specified inside the `$apiMethods` variable. Examples can be found below.

### Turning off API key authentication for a specific method

By default, all the methods in the ApiGuardController will be authenticated. To turn this off for a specific method, use the `keyAuthentication` option.

```php
<?php

use Chrisbjr\ApiGuard\Controllers\ApiGuardController;

class BooksController extends ApiGuardController
{

    protected $apiMethods = [
        'show' => [
            'keyAuthentication' => false
        ],
    ];

    public function all()
    {
        $books = Book::all();

        return $this->response->withCollection($books, new BookTransformer);
    }
    
    public function show($id)
    {
        try {
            $book = Book::findOrFail($id);
            
            return $this->response->withItem($book, new BookTransformer);
        } catch (ModelNotFoundException $e) {
            return $this->response->errorNotFound();
        }
    }

}
```

This above example will turn off key authentication for the `show` method.

### Specifying access levels for API methods

If you take a look at the `api_keys` table in your database, you will notice that there is a `level` field.

This will allow you to specify a level for your API key and if the method has a higher level than the API key, access will be restricted. Here is an example on how to set the level on a method:

```php
<?php

use Chrisbjr\ApiGuard\Controllers\ApiGuardController;

class BooksController extends ApiGuardController
{

    protected $apiMethods = [
        'show' => [
            'level' => 10
        ],
    ];
    
    ...

}
```

Now if your API key has a level of 9 or lower, then access to the `show` method will be restricted.

### Limiting API key access rate

You can limit the rate at which an API key can have access to a particular method by using the `limits.key` option.


```php
<?php

use Chrisbjr\ApiGuard\Controllers\ApiGuardController;

class BooksController extends ApiGuardController
{

    protected $apiMethods = [
        'show' => [
            'limits' => [
                'key' => [
                    'increment' => '1 hour',
                    'limit' => 100
                ]
            ]
        ],
    ];
    
    ...

}
```

The above example will limit the access of a particular API key to 100 requests for every hour.

Note: The `increment` option can be any value that is accepted by the `strtotime()` method.

### Limiting access to a method

There is also an option to limit the request rate for a given method no matter what API key is used. For this, we use the `limits.method` option.

```php
<?php

use Chrisbjr\ApiGuard\Controllers\ApiGuardController;

class BooksController extends ApiGuardController
{

    protected $apiMethods = [
        'show' => [
            'limits' => [
                'method' => [
                    'increment' => '1 day',
                    'limit' => 1000
                ]
            ]
        ],
    ];
    
    ...

}
```

The above example will limit the request rate to the `show` method to 1000 requests per day.

Note: The `increment` option can be any value that is accepted by the `strtotime()` method.

## Accessing the User instance and Stateless authentication

You can easily access the User instance from the belongsTo() relationship of the ApiKey model to the User class. With this, we can implement API based authentication with the following as an example. 

Note that while we have utilized [Confide](https://github.com/zizaco/confide) for handling the credential checking, you can have your own way of having this done (like using the native Laravel Auth class, or [Sentry](https://github.com/cartalyst/sentry) for that matter).

```php
<?php namespace api\v1;

use Chrisbjr\ApiGuard\Controllers\ApiGuardController;
use Chrisbjr\ApiGuard\Models\ApiKey;
use Chrisbjr\ApiGuard\Transformers\ApiKeyTransformer;
use Confide;
use Input;
use User;
use Validator;

class UserApiController extends ApiGuardController
{
    protected $apiMethods = [
        'authenticate' => [
            'keyAuthentication' => false
        ]
    ];

    public function authenticate() {
        $credentials['username'] = Input::json('username');
        $credentials['password'] = Input::json('password');

        $validator = Validator::make([
                'username' => $credentials['username'],
                'password' => $credentials['password']
            ],
            [
                'username' => 'required|max:255',
                'password' => 'required|max:255'
            ]
        );

        if ($validator->fails()) {
            return $this->response->errorWrongArgsValidator($validator);
        }

        try {
            $user                 = User::whereUsername($credentials['username'])->first();
            $credentials['email'] = $user->email;
        } catch (\ErrorException $e) {
            return $this->response->errorUnauthorized("Your username or password is incorrect");
        }

        if (Confide::logAttempt($credentials) == false) {
            return $this->response->errorUnauthorized("Your username or password is incorrect");
        }

        // We have validated this user
        // Assign an API key for this session
        $apiKey = ApiKey::where('user_id', '=', $user->id)->first();
        if (!isset($apiKey)) {
            $apiKey                = new ApiKey;
            $apiKey->user_id       = $user->id;
            $apiKey->key           = $apiKey->generateKey();
            $apiKey->level         = 5;
            $apiKey->ignore_limits = 0;
        } else {
            $apiKey->generateKey();
        }

        if (!$apiKey->save()) {
            return $this->response->errorInternalError("Failed to create an API key. Please try again.");
        }

        // We have an API key.. i guess we only need to return that.
        return $this->response->withItem($apiKey, new ApiKeyTransformer);
    }

    public function getUserDetails() {
        $user = $this->apiKey->user;

        return isset($user) ? $user : $this->response->errorNotFound();
    }

    public function deauthenticate() {
        if (empty($this->apiKey)) {
            return $this->response->errorUnauthorized("There is no such user to deauthenticate.");
        }

        $this->apiKey->delete();

        return $this->response->withArray([
            'ok' => [
                'code'      => 'SUCCESSFUL',
                'http_code' => 200,
                'message'   => 'User was successfuly deauthenticated'
            ]
        ]);
    }
}
```
