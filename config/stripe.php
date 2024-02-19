<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User model
    |--------------------------------------------------------------------------
    */
    'model'  => App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | User email key on model
    |--------------------------------------------------------------------------
    */
    'email_key' => 'email',

    /*
    |--------------------------------------------------------------------------
    | User name key on model
    |--------------------------------------------------------------------------
    */
    'name_key' => 'name',

    /*
    |--------------------------------------------------------------------------
    | Stripe integration keys
    |--------------------------------------------------------------------------
    */
    'key'    => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'app_id' => env('STRIPE_APP_ID'),

    /*
    |--------------------------------------------------------------------------
    | Webhooks
    |--------------------------------------------------------------------------
    */
    'webhooks' => [
        'signature_tolerance' => env('STRIPE_WEBHOOKS_SIGNATURE_TOLERANCE', \Stripe\Webhook::DEFAULT_TOLERANCE),
        'signing_secrets' => [
            'checkout' => env('STRIPE_WEBHOOK_CHECKOUT'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Currencies
    |--------------------------------------------------------------------------\
    |
    | If your application only supports specific currencies, you can list
    | them here. An empty array indicates that ALL currencies are supported.
    |
    */
    'currencies' => [
        'PLN',
        'GBP',
        'EUR',
        'USD',
    ],

    /*
    |--------------------------------------------------------------------------
    | Minimum Charge Amounts
    |--------------------------------------------------------------------------
    |
    | The minimum charge amounts.
    |
    | @see https://stripe.com/docs/currencies#minimum-and-maximum-charge-amounts
    */
    'minimum_charge_amounts' => [
        'USD' => 50,
        'AED' => 200,
        'AUD' => 50,
        'BGN' => 100,
        'BRL' => 50,
        'CAD' => 50,
        'CHF' => 50,
        'CZK' => 1500,
        'DKK' => 250,
        'EUR' => 50,
        'GBP' => 30,
        'HKD' => 400,
        'HUF' => 15700,
        'INR' => 50,
        'JPY' => 50,
        'MXN' => 1000,
        'MYR' => 200,
        'NOK' => 300,
        'NZD' => 50,
        'PLN' => 200,
        'RON' => 200,
        'SEK' => 300,
        'SGD' => 50,
        'THB' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Zero-decimal currencies
    |--------------------------------------------------------------------------\
    |
    | All API requests expect amounts to be provided in a currency’s smallest unit.
    | For example, to charge 10 USD, provide an amount value of 1000 (that is, 1000 cents).
    | For zero-decimal currencies, still provide amounts as an integer but without multiplying by 100.
    | For example, to charge ¥500, provide an amount value of 500.
    |
    */
    'zero_decimal_currencies' => [
        'BIF',
        'CLP',
        'DJF',
        'GNF',
        'JPY',
        'KMF',
        'KRW',
        'MGA',
        'PYG',
        'RWF',
        'UGX',
        'VND',
        'VUV',
        'XAF',
        'XOF',
        'XPF'
    ],

    /*
    |--------------------------------------------------------------------------
    | Three-decimal currencies
    |--------------------------------------------------------------------------\
    |
    | The API supports three-decimal currencies for the standard payment flows,
    | including Payment Intents, Refunds, and Disputes. However,
    | to ensure compatibility with Stripe’s payments partners,
    | these API calls require the least-significant (last) digit to be 0.
    | Your integration must round amounts to the nearest ten. For example,
    | 5.124 KWD must be rounded to 5120 or 5130.
    |
    */
    'three_decimal_currencies' => [
        'BHD',
        'JOD',
        'KWD',
        'OMR',
        'TND'
    ],
];
