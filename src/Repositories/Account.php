<?php

namespace Dominservice\LaraStripe\Repositories;

use Dominservice\LaraStripe\Exception\ParameterBadValueException;
use Dominservice\LaraStripe\Models\StripeAccount as StripeAccountModel;
use Stripe\Exception\ApiErrorException;
use Stripe\Service\AccountService;

class Account extends Repositories
{
    /**
     * @var array
     */
    protected array $allowedParameters = [
        'type',
        'business_type',
        'capabilities',
        'company',
        'country',
        'email',
        'individual',
        'metadata',
        'tos_acceptance',
        'account_token',
        'business_profile',
        'default_currency',
        'documents',
        'external_account',
        'settings',
    ];
    public function __construct(
        protected null|AccountService $accounts,
        protected readonly null|string $stripeAccount = null
    )
    {}

    /**
     * @param $params
     * @return \Stripe\Collection
     * @throws ApiErrorException
     */
    public function all($params = []): \Stripe\Collection
    {
        $data =  $this->accounts->all($this->getParams($params), $this->getOpts());
        $this->clearObjectParams();

        return $data;
    }

    /**
     * @param $accountId
     * @return \Stripe\Account
     * @throws ApiErrorException
     */
    public function retrieve($accountId): \Stripe\Account
    {
        $this->getAccountIdByUser($accountId);
        $data = $this->accounts->retrieve($accountId, $this->getParams(), $this->getOpts());
        $this->clearObjectParams();

        return $data;
    }

    /**
     * @param $user
     * @param $token
     * @return \Stripe\Account
     * @throws ApiErrorException
     */
    public function create($user, $token = null): \Stripe\Account
    {
        if (empty($this->params['email'])) {
            $email = config('stripe.email_key');
            $this->params['email'] = $user->$email;
        }
        if (empty($this->params['email'])) {
            $name = config('stripe.name_key');
            $this->params['name'] = $user->$name;
        }

        if ($token) {
            $this->params['source'] = $token;
        }

        $accountObject = $this->accounts->create($this->getParams(), $this->getOpts());
        $this->createAccountModel($user, $accountObject);
        $this->clearObjectParams();
        return $accountObject;
    }

    /**
     * @param $accountId
     * @param $params
     * @return \Stripe\Account
     * @throws ApiErrorException
     */
    public function update($accountId, $params): \Stripe\Account
    {
        $this->getAccountIdByUser($accountId);
        $data = $this->accounts->update($accountId, $this->getParams($params), $this->getOpts());
        $this->clearObjectParams();

        return $data;
    }

    /**
     * @param $accountId
     * @return \Stripe\Account
     * @throws ApiErrorException
     */
    public function delete($accountId): \Stripe\Account
    {
        $this->getAccountIdByUser($accountId);
        $data = $this->accounts->delete($accountId, $this->getParams(), $this->getOpts());
        $this->clearObjectParams();

        return $data;
    }

    /**
     * @param $param
     * @param $key
     * @param $val
     * @return void
     * @throws ParameterBadValueException
     */
    protected function validateParam($param, $key, $val = null): void
    {
        if (isset($this->extendObjects[$param])) {

            $this->setExtendObjectsParam($param, $key, $val);
        } else {
            $val = $key;

            if ($param === 'type'
                && (!is_string($val) || (is_string($val) && !in_array($val, ['custom', 'express', 'standard'])))
            ) {
                throw new ParameterBadValueException("The 'type' parameter has an invalid value. Check the documentation: https://docs.stripe.com/api/accounts/create#create_account-type");

            } elseif ($param === 'business_type'
                && (!is_string($val) || (is_string($val) && !in_array($val, ['company', 'government_entity', 'individual', 'non_profit'])))
            ) {
                throw new ParameterBadValueException("The 'business_type' parameter has an invalid value. Check the documentation: https://docs.stripe.com/api/accounts/create#create_account-business_type");

            }


            $this->setParam($param, $val);
        }
    }

    /**
     * @param $user
     * @param $emptyModel
     * @return bool|StripeAccountModel
     */
    protected function getAccountModel($user, $emptyModel = false)
    {
        if (!$stripeAccount = StripeAccountModel::where('user_id', $user->{$user->getKeyName()})->first() && $emptyModel) {
            $stripeAccount = new StripeAccountModel();
            $stripeAccount->user_id = $user->{$user->getKeyName()};
            $stripeAccount->save();
        }

        return $stripeAccount;
    }

    /**
     * @param $user
     * @param \Stripe\Account $accountObject
     * @param $typeAccount
     * @return bool|StripeAccountModel
     */
    protected function createAccountModel($user, \Stripe\Account $accountObject, $typeAccount = null)
    {
        $userForStripe = $this->getAccountModel($user, true);

        if (!$userForStripe->stripe_account_id) {
            $typeAccount = $typeAccount && in_array($typeAccount, [
                \Stripe\Account::TYPE_CUSTOM,
                \Stripe\Account::TYPE_EXPRESS,
                \Stripe\Account::TYPE_STANDARD
            ])
                ? $typeAccount
                : \Stripe\Account::TYPE_CUSTOM;
            $userForStripe->stripe_account_id = $accountObject->id;
            $userForStripe->type_account = $typeAccount;
            $userForStripe->save();
        }

        return $userForStripe;
    }

    /**
     * @param $user
     * @return void
     */
    private function getAccountIdByUser(&$user)
    {
        if (is_a($user, get_class(new (config('stripe.model'))))) {
            if ($userForStripe = $this->getAccountModel($user)) {
                $user = $userForStripe->stripe_account_id;
            } else {
                $user = '_empty_';
            }
        }
    }
}