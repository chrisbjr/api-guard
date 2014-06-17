ApiGuard
========

A simple way of authenticating your APIs with API keys using Laravel

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

    $ curl -X POST http://localhost:8000/apiguard/generate

This will generate an API key and should return the following data:

    {
        code: 201
        status: "Created"
        data: {
            key: "2ed9d72e5596800bf805ca1c735e446df72019ef"
            user_id: 0
            level: 10
            ignore_limits: 1
            updated_at: "2014-06-15 15:33:14"
            created_at: "2014-06-15 15:33:14"
            id: 1
        }
    }

Take note of your first API key.

Now, to prevent others from generating API keys through the route above, you can disable this in ApiGuard's configuration file.

To create your own configuration file for ApiGuard, run the following command:

    $ php artisan config:publish chrisbjr/api-guard

The configuration file will be found in `app/config/packages/chrisbjr/api-guard/config.php`. Open this file and change the `generateApiRoute` variable to `false`

    'generateApiRoute' => false

## Usage

Basic usage of ApiGuard is to create a controller and extend that class to use the `ApiGuardController`.

    <?php
    use Chrisbjr\ApiGuard\ApiGuardController;

    class ObjectApiController extends ApiGuardController
    {
        public getObject() 
        {
            $object_data = array(
                'id' => 1,
                'name' => 'object'
            );

            return $this->response($data, 200);
        }
    }
    
You can access the above controller by creating a basic route in your `app/routes.php`:

    Route::get('api/v1/objects', 'ObjectApiController@getObject');

You will need to use your API key and put it in the header to access it. By default, the header value is named `X-API-KEY`. You can change this in the config file.

Try calling this route using `curl`

     curl --header "X-API-KEY: 2ed9d72e5596800bf805ca1c735e446df72019ef" http://localhost:8000/api/v1/objects

You should get the following response:

    {
        code: 200
        status: "OK"
        data: {
            id: 1,
            name: "object"
        }
    }

