ApiGuard
========

A simple way of authenticating your APIs with API keys using Laravel. This package uses the following libraries:

- philsturgeon's [Fractal](https://github.com/thephpleague/fractal)
- maximebeaudoin's [api-response](https://github.com/ellipsesynergie/api-response)

The concept for managing API keys is also taken from Phil Sturgeon's [codeigniter-restserver](https://github.com/philsturgeon/codeigniter-restserver).
I've been looking for an equivalent for Laravel but did not find any so this is an implementation for that.

## Quick start

### Required setup

In the `require` key of `composer.json` file add the following

    "chrisbjr/api-guard": "dev-master"

Run the Composer update comand

    $ composer update

In your `config/app.php` add `'Chrisbjr\ApiGuard\ApiGuardServiceProvider'` to the end of the `$providers` array

    'providers' => array(

        'Illuminate\Foundation\Providers\ArtisanServiceProvider',
        'Illuminate\Auth\AuthServiceProvider',
        ...
        'EllipseSynergie\ApiResponse\Laravel\ResponseServiceProvider',
        'Chrisbjr\ApiGuard\ApiGuardServiceProvider',

    ),

Now generate the api-guard migration (make sure you have your database configuration set up correctly):

    $ php artisan migrate --package="chrisbjr/api-guard"

It will setup two tables - api_keys and api_logs.

### Generating your first API key

Once you're done with the required setup, you can now generate your first API key.

Make sure your Laravel installation is accessible through a web server - if not, you can use `artisan` to quickly bring up your Laravel installation by running the command below:

    $ php artisan serve

Once the web server is up, you can issue a POST request ApiGuard's pre-defined route for generating an API key. You can use `curl` in the command line as shown below:

    $ curl -X POST http://localhost:8000/apiguard/api_key

This will generate an API key and should return the following data:

    {
        data: {
            id: 9
            user_id: 0
            key: "7f03891b8f7c4ba10af2e0e37232f98fa2fc9a1a"
            level: 10
            ignore_limits: 1
            created_at: {
                date: "2014-06-26 12:07:49"
                timezone_type: 3
                timezone: "UTC"
            }
            updated_at: {
                date: "2014-06-26 12:07:49"
                timezone_type: 3
                timezone: "UTC"
            }
        }
    }

Take note of your first API key.

Now, to prevent others from generating API keys through the route above, you can disable this in ApiGuard's configuration file.

To create your own configuration file for ApiGuard, run the following command:

    $ php artisan config:publish chrisbjr/api-guard

The configuration file will be found in `app/config/packages/chrisbjr/api-guard/config.php`. Open this file and change the `generateApiKeyRoute` variable to `false`

    'generateApiKeyRoute' => false

Generally, you will want to generate API keys for each user in your application. The `api_keys` table has a `user_id` field which you can populate for your users.

## Usage

Basic usage of ApiGuard is to create a controller and extend that class to use the `ApiGuardController`.

    <?php
    use Chrisbjr\ApiGuard\ApiGuardController;

    class BooksController extends ApiGuardController
    {
        protected $apiMethods = [
            'all' => [
                'keyAuthentication' => true,
                'level' => 1,
                'limits' => [
                    // The variable below sets API key limits
                    'key' => [
                        'increment' => '1 hour',
                        'limit' => 100
                    ],
                    // The variable below sets API method limits
                    'method' => [
                        'increment' => '1 day',
                        'limit' => 1000
                    ]
                ]
            ],
            
            'show' => [
                'keyAuthentication' => false
            ]
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
            } catch (ModelNotFoundException $e) {
                return $this->response->errorNotFound();
            }
        }
    }

Notice the `$apiMethods` variable. You can set `limits`s , `level`s, and `keyAuthentication` for each method here.
If you don't specify any, the defaults would be that no limits would be implemented, no level access, and key authentication would be required.

You should also be able to use the api-response object by using `$this->response`. More examples can be found on the Github page: [https://github.com/ellipsesynergie/api-response](https://github.com/ellipsesynergie/api-response).

You can access the above controller by creating a basic route in your `app/routes.php`:

    Route::get('api/v1/books', 'BooksController@all');
    Route::get('api/v1/books/{id}', 'BooksController@show');

You will need to use your API key and put it in the header to access it. By default, the header value is using the `Authorization` parameter. You can change this in the config file.

Try calling this route using `curl`

     curl --header "Authorization: 2ed9d72e5596800bf805ca1c735e446df72019ef" http://localhost:8000/api/v1/books

You should get the following response:

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

### Accessing the User instance

You can easily access the User instance from the belongsTo() relationship of the ApiKey model to the User class. With this, we can implement API based authentication with the following as an example. 

Note that while we have utilized [Confide](https://github.com/zizaco/confide) for handling the credential checking, you can have your own way of having this done (like using the native Laravel Auth class, or [Sentry](https://github.com/cartalyst/sentry) for that matter).

```
<?php


namespace api\v1;

use Chrisbjr\ApiGuard\ApiGuardController;
use Chrisbjr\ApiGuard\ApiKey;
use Chrisbjr\ApiGuard\Transformers\ApiKeyTransformer;
use Confide;
use Input;
use User;
use Validator;

class UserApiController extends ApiGuardController
{
    protected $apiMethods = array(
        'authenticate'   => array(
            'keyAuthentication' => false
        ),
        'deauthenticate' => array(
            'keyAuthentication' => true
        )
    );

    public function authenticate() {
        $credentials['username'] = Input::json('username');
        $credentials['password'] = Input::json('password');

        $validator = Validator::make(array(
                'username' => $credentials['username'],
                'password' => $credentials['password']
            ),
            array(
                'username' => 'required|max:255',
                'password' => 'required|max:255'
            )
        );

        if ($validator->fails()) {
            return $this->response->errorWrongArgsValidator($validator);
        }

        try {
            $user = User::whereUsername($credentials['username'])->first();
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

    public function deauthenticate() {
        if (empty($this->apiKey)) {
            return $this->response->errorUnauthorized("There is no such user to deauthenticate.");
        }

        $this->apiKey->delete();

        return $this->response->withArray([
            'ok' => [
                'code' => 'SUCCESSFUL',
                'http_code' => 200,
                'message' => 'User was successfuly deauthenticated'
            ]
        ]);
    }
}

```
