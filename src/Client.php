<?php

namespace Dominservice\LaraStripe;

use Dominservice\LaraStripe\Repositories\Account;
use Dominservice\LaraStripe\Repositories\AccountLink;
use Dominservice\LaraStripe\Repositories\BillingPortalSession;
use Dominservice\LaraStripe\Repositories\CheckoutSession;
use Dominservice\LaraStripe\Repositories\Customers;
use Dominservice\LaraStripe\Repositories\Invoices;
use Dominservice\LaraStripe\Repositories\LoginLink;
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
     * @return Account
     */
    public function accounts(): Account
    {
        return new Account($this->client->accounts);
    }

    /**
     * @return AccountLink
     */
    public function accountLinks(): AccountLink
    {
        return new AccountLink($this->client->accountLinks);
    }

    /**
     * @return LoginLink
     */
    public function loginLinks(): LoginLink
    {
        return new LoginLink($this->client->accounts);
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

    /**
     * @return Invoices
     */
    public function invoices(): Invoices
    {
        return new Invoices($this->client->invoices);
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
