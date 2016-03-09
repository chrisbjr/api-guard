ApiGuard
========

[![Latest Stable Version](https://poser.pugx.org/chrisbjr/api-guard/v/stable)](https://packagist.org/packages/chrisbjr/api-guard) [![Total Downloads](https://poser.pugx.org/chrisbjr/api-guard/downloads)](https://packagist.org/packages/chrisbjr/api-guard) 

[![Join the chat at https://gitter.im/chrisbjr/api-guard](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/chrisbjr/api-guard?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

A simple way of authenticating your APIs with API keys using Laravel. This package uses the following libraries:

- philsturgeon's [Fractal](https://github.com/thephpleague/fractal)
- maximebeaudoin's [api-response](https://github.com/ellipsesynergie/api-response)

The concept for managing API keys is also taken from Phil Sturgeon's [codeigniter-restserver](https://github.com/philsturgeon/codeigniter-restserver).
I've been looking for an equivalent for Laravel but did not find any so this is an implementation for that.

## Laravel 5 is finally supported!

**Laravel 5.2.*: `~3.*`

**Laravel 5.1.*: `~2.*`

**Laravel 4.2.*: [`~1.*`](https://github.com/chrisbjr/api-guard/tree/laravel4) (Recently updated version for Laravel 4. Please note that there are namespace changes here)

**Laravel 4.2.*: [`0.*`](https://github.com/chrisbjr/api-guard/tree/v0.7) (The version that most of you are using)

## Quick start

### Laravel 5.2.x

Run `composer require chrisbjr/api-guard 3.1.*`

In your `config/app.php` add `Chrisbjr\ApiGuard\Providers\ApiGuardServiceProvider` to the end of the `providers` array

```php
'providers' => array(

    ...
    Chrisbjr\ApiGuard\Providers\ApiGuardServiceProvider::class,
),
```

Add the `ApiGuardAuth` facade to the end of the `aliases` array as well

```php
'aliases' => array(

    ...
    'ApiGuardAuth' => \Chrisbjr\ApiGuard\Facades\ApiGuardAuth::class,
),
```

Now publish the migration and configuration files for api-guard:

    $ php artisan vendor:publish --provider="Chrisbjr\ApiGuard\Providers\ApiGuardServiceProvider"

Then run the migration:

    $ php artisan migrate

It will setup two tables - api_keys and api_logs.

### Laravel 5.0.x to 5.1.x

Note: Documentation for use with Laravel 5.0.x and 5.1.x differs from Laravel 5.2.x. Please refer to the README [here](https://github.com/chrisbjr/api-guard/tree/v2.3.0).

### Laravel 4.2.x

Note: Documentation for use with Laravel 4.2.x differs from Laravel 5.0.x. Please refer to the README [here](https://github.com/chrisbjr/api-guard/tree/v1.0). If you are using version `0.*` you can find the README [here](https://github.com/chrisbjr/api-guard/tree/v0.7)

### Generating your first API key

Once you're done with the required setup, you can now generate your first API key.

Run the following command to generate an API key:

`php artisan api-key:generate`

Generally, you will want to generate API keys for each user in your application. The `api_keys` table has a `user_id` field which you can populate for your users.

To generate an API key that is linked to a user, you can do the following:

`php artisan api-key:generate --user-id=1`

To generate an API key from within your application, you can use the following method in the `ApiKey` model:

```
$apiKey = Chrisbjr\ApiGuard\Models\ApiKey::make()
```

## Usage

Basic usage of ApiGuard is to create a controller and extend that class to use the `ApiGuardController`.

Note: The namespace of the `ApiGuardController` differs from previous versions.

```php
<?php

use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;

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

use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;

class BooksController extends ApiGuardController
{

    protected $apiMethods = [
        'show' => [
            'keyAuthentication' => false
        ],
    ];

    ...

}
```

This above example will turn off key authentication for the `show` method.

### Specifying access levels for API methods

If you take a look at the `api_keys` table in your database, you will notice that there is a `level` field.

This will allow you to specify a level for your API key and if the method has a higher level than the API key, access will be restricted. Here is an example on how to set the level on a method:

```php
<?php

use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;

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

use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;

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

The above example will limit the access to the `show` method of an API key to 100 requests for every hour.

Note: The `increment` option can be any value that is accepted by the `strtotime()` method.

### Limiting access to a method

There is also an option to limit the request rate for a given method no matter what API key is used. For this, we use the `limits.method` option.

```php
<?php

use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;

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

### Logging at method level

You can set logging at method level by using the `logged` option.

```php
<?php

use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;

class BooksController extends ApiGuardController
{

    protected $apiMethods = [
        'show' => [
            'logged' => true
        ]
    ];
    
    ...

}
```

By default for all methods in api-guard, the option `logged` is set to true. Set it to `false` to exclude that method for logging.
