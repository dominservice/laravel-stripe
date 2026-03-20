<?php

namespace Dominservice\LaraStripe\Repositories;

use Dominservice\LaraStripe\Exception\NoParametersException;
use Stripe\Exception\ApiErrorException;
use Stripe\Service\AccountService;

class LoginLink extends Repositories
{
    public function __construct(
        protected null|AccountService $accounts,
        protected readonly null|string $stripeAccount = null
    )
    {}

    /**
     * @param string $accountId
     * @param array $params
     * @return \Stripe\LoginLink
     * @throws ApiErrorException
     * @throws NoParametersException
     */
    public function create(string $accountId, array $params = []): \Stripe\LoginLink
    {
        if ($accountId === '') {
            throw new NoParametersException("The account id is required. Check the documentation: https://docs.stripe.com/api/accounts/login_link/create");
        }

        $this->object = $this->accounts->createLoginLink($accountId, $params, $this->getOpts());
        $this->clearObjectParams();

        return $this->object;
    }
}
