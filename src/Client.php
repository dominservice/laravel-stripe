<?php

namespace Dominservice\LaraStripe;

use Dominservice\LaraStripe\Repositories\Customers;
use Dominservice\LaraStripe\Repositories\Prices;
use Dominservice\LaraStripe\Repositories\Products;
use Dominservice\LaraStripe\Repositories\Subscriptions;
use Stripe\StripeClient;

class Client
{
    /**
     * @var StripeClient
     */
    private StripeClient $client;

    public function __construct()
    {
        $this->client = new StripeClient(config('stripe.secret'));
    }

    /**
     * @return Customers
     */
    public function customers(): Customers
    {
        return new Customers($this->client->customers);
    }

    /**
     * @return Products
     */
    public function products(): Products
    {
        return new Products($this->client->products, $this->client->prices);
    }

    /**
     * @return Prices
     */
    public function prices(): Prices
    {
        return new Prices($this->client->prices);
    }

    /**
     * @return Subscriptions
     */
    public function subscription(): Subscriptions
    {
        return new Subscriptions($this->client->subscriptions);
    }
}