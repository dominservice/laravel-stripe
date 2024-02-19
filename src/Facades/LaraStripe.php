<?php

namespace Dominservice\LaraStripe\Facades;


use Dominservice\LaraStripe\Client;
use Dominservice\LaraStripe\ConnectClient;
use Illuminate\Support\Facades\Facade;

/**
 * Class LaraStripe
 *
 * @package Dominservice\LaraStripe
 *
 * @method static Client client(string $key)
 * @method static ConnectClient connectClient(string $key)
 *
 */
class LaraStripe extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'lara_stripe';
    }
}