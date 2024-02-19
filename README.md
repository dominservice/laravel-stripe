# Laravel Stripe 

[![Packagist](https://img.shields.io/packagist/v/dominservice/laravel-stripe.svg)]()
[![Latest Version](https://img.shields.io/github/release/dominservice/laravel-stripe.svg?style=flat-square)](https://github.com/dominservice/laravel-stripe/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/dominservice/laravel-stripe.svg?style=flat-square)](https://packagist.org/packages/dominservice/laravel-stripe)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Stripe integration for laravel 10+ on PHP 8.1+

## Installation

```shell
composer require Dominservice/laravel-stripe
```

Add your stripe credentials in `.env`:

```enviroment
STRIPE_APP_ID=ca_XxxXXxXXX
STRIPE_KEY=pk_live_XxxXXxXXX
STRIPE_SECRET=sk_live_XxxXXxXXX
STRIPE_WEBHOOK_CHECKOUT=whsec_XxxXXxXXX
```
Publish config:

```shell
php artisan vendor:publish --tag=stripe
```


Publish migrations:

```shell
php artisan vendor:publish --tag=stripe-migrations
```

## Configuration

In the configuration file you can change the list of allowed currencies. 
Check if your currency is on this list, and if it is not there, add it, otherwise you will not be able to use this package.

Also check if your User model is the same as the model specified in the `config/stripe.php` file

# Usage

## Routing

You must attach middleware `stripe.verify` to routes that have a reference to __payments webhook__ so that the paths are secured.

```php
Route::group(['middleware' => ['stripe.verify'], 'namespace' => '\App\Http\Controllers'], function () {
    Route::get('/webhook/payments', 'WebhookController@payments');
    
    (...)
```

## Code
 __To be continued...__ ;) 

This repo in not finished
