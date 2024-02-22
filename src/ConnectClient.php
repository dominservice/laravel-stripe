<?php

namespace Dominservice\LaraStripe;

use Dominservice\LaraStripe\Repositories\Account;
use Dominservice\LaraStripe\Repositories\BillingPortalSession;
use Dominservice\LaraStripe\Repositories\CheckoutSession;
use Dominservice\LaraStripe\Repositories\Customers;
use Dominservice\LaraStripe\Repositories\Prices;
use Dominservice\LaraStripe\Repositories\Products;
use Dominservice\LaraStripe\Repositories\Subscriptions;
use Stripe\StripeClient;

class ConnectClient
{
    /**
     * @var StripeClient
     */
    private StripeClient $client;

    /**
     * @param string $stipeAccount
     */
    public function __construct(private string $stipeAccount)
    {
        $this->client = new StripeClient(config('stripe.secret'));
    }

    /**
     * @return Customers
     */
    public function customers(): Customers
    {
        return new Customers($this->client->customers, $this->stipeAccount);
    }

    /**
     * @return Account
     */
    public function accounts(): Account
    {
        return new Account($this->client->accounts, $this->stipeAccount);
    }

    /**
     * @return Products
     */
    public function products(): Products
    {
        return new Products($this->client->products, $this->client->prices, $this->stipeAccount);
    }

    /**
     * @return Prices
     */
    public function prices(): Prices
    {
        return new Prices($this->client->prices, null, $this->stipeAccount);
    }

    /**
     * @return Subscriptions
     */
    public function subscription(): Subscriptions
    {
        return new Subscriptions($this->client->subscriptions);
    }

    /**
     * @return CheckoutSession
     */
    public function checkoutSessions(): CheckoutSession
    {
        return new CheckoutSession($this->client->checkout->sessions);
    }

    /**
     * @return BillingPortalSession
     */
    public function billingPortalSession(): BillingPortalSession
    {
        return new BillingPortalSession($this->client->billingPortal->sessions);
    }
}
