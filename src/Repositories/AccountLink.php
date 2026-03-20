<?php

namespace Dominservice\LaraStripe\Repositories;

use Dominservice\LaraStripe\Exception\NoParametersException;
use Dominservice\LaraStripe\Exception\ParameterBadValueException;
use Stripe\Exception\ApiErrorException;
use Stripe\Service\AccountLinkService;

class AccountLink extends Repositories
{
    protected array $allowedParameters = [
        'account',
        'collect',
        'collection_options',
        'refresh_url',
        'return_url',
        'type',
    ];

    public function __construct(
        protected null|AccountLinkService $accountLinks,
        protected readonly null|string $stripeAccount = null
    )
    {}

    /**
     * @throws ApiErrorException
     * @throws NoParametersException
     */
    public function create(): \Stripe\AccountLink
    {
        if (empty($this->params['account'])) {
            throw new NoParametersException("The 'account' parameter is required. Check the documentation: https://docs.stripe.com/api/account_links/create#account_link_create-account");
        }

        if (empty($this->params['refresh_url'])) {
            throw new NoParametersException("The 'refresh_url' parameter is required. Check the documentation: https://docs.stripe.com/api/account_links/create#account_link_create-refresh_url");
        }

        if (empty($this->params['return_url'])) {
            throw new NoParametersException("The 'return_url' parameter is required. Check the documentation: https://docs.stripe.com/api/account_links/create#account_link_create-return_url");
        }

        if (empty($this->params['type'])) {
            throw new NoParametersException("The 'type' parameter is required. Check the documentation: https://docs.stripe.com/api/account_links/create#account_link_create-type");
        }

        $this->object = $this->accountLinks->create($this->getParams(), $this->getOpts());
        $this->clearObjectParams();

        return $this->object;
    }

    /**
     * @throws ParameterBadValueException
     */
    protected function validateParam($param, $key, $val = null): void
    {
        $val = $key;

        if ($param === 'type' && !in_array($val, ['account_onboarding', 'account_update'], true)) {
            throw new ParameterBadValueException("The 'type' parameter should be one of 'account_onboarding', 'account_update'.");
        }

        if ($param === 'collect' && !in_array($val, ['currently_due', 'eventually_due'], true)) {
            throw new ParameterBadValueException("The 'collect' parameter should be one of 'currently_due', 'eventually_due'.");
        }

        $this->setParam($param, $val);
    }
}
