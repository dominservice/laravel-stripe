<?php

namespace Dominservice\LaraStripe;

class LaraStripeService
{
    /**
     * @var bool
     */
    public static $runMigrations = true;

    /**
     * @return Client
     */
    public function client(): Client
    {
        return new Client();
    }

    /**
     * @param $stipeAccount
     * @return Client|ConnectClient
     */
    public function connectClient($stipeAccount): Client|ConnectClient
    {
        return new ConnectClient($stipeAccount);
    }
}