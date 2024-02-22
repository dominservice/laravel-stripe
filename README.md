# Laravel Stripe 

[![Packagist](https://img.shields.io/packagist/v/dominservice/laravel-stripe.svg)]()
[![Latest Version](https://img.shields.io/github/release/dominservice/laravel-stripe.svg?style=flat-square)](https://github.com/dominservice/laravel-stripe/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/dominservice/laravel-stripe.svg?style=flat-square)](https://packagist.org/packages/dominservice/laravel-stripe)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Stripe integration for laravel 10+ on PHP 8.1+

## Installation

```shell
composer require dominservice/laravel-stripe
```

Add your stripe credentials in `.env`:

```enviroment
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

You must attach middleware `stripe.verify:{name}` to routes that have a reference to __payments webhook__ so that the paths are secured.

```php
Route::group(['middleware' => ['stripe.verify:checkout'], 'namespace' => '\App\Http\Controllers'], function () {
    Route::get('/webhook/payments', 'WebhookController@payments');
    
    (...)
```

`{name}` indicates which webhook key should be used. In the above example, it is `checkout`.

This means that `config("stripe.webhooks.signing_secrets.checkout")` will be used to verify webhook.

## Code

```php
use Dominservice\LaraStripe\Client as StripeClient;

(...)

$stripe = (new StripeClient());

// Create product with price
$product = \App\Models\Product::find(1);
$productStripe = $stripe->products()
    ->setName($product->name)
    ->setActive(true)
    ->setExtendPricesCurrency('pln')
    ->setExtendPricesUnitAmount((float)$amount)
    ->setExtendPricesBillingScheme('per_unit')
    ->setExtendPricesRecurring(['interval' => 'month'])
    ->create($product);

// Create customer
$customer = $stripe->customers()
    ->setName($user->name)
    ->setEmail($user->email)
    ->setPhone($user->phone)
    ->setAddress([
        'country' => 'PL',
        'city' => 'Warszawa',
        'postal_code' => '00-000',
        'line1' => 'ul. kopernika 1/2',
    ])
    ->create($user);

// Checkout session
$session = $stripe->checkoutSessions()
    ->setSuccessUrl(route('payment.afterTransaction', $order->ulid) . '?session_id={CHECKOUT_SESSION_ID}')
    ->setCancelUrl(route('payment.canceled', $order->ulid))
    ->setMode('subscription')
    ->setClientReferenceId($order->ulid)
    ->setCustomer($customer->id)
    ->setLineItems([
        [
            'price' => $productStripe->default_price->id,
            'quantity' => 1,
        ]
     ])
     ->create();

```

 __To be continued...__ ;) 



This repo is not finished
