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

Now generate the api-guard migration:

    $ php artisan migrate --package="chrisbjr/api-guard"

It will setup two tables - api_keys and api_logs.

## Usage

Create a controller that extends ApiController:

    <?php
    use Chrisbjr\ApiGuard\ApiController;

    class IpController extends ApiController
    {
        public getObject() 
        {
          // Your code here
        }
    }
    
That's it! Updates to this documentation coming soon!
