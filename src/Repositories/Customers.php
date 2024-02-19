<?php

namespace Dominservice\LaraStripe\Repositories;

use Dominservice\LaraStripe\Exception\ParameterBadValueException;
use Dominservice\LaraStripe\Models\StripeCustomer as StripeCustomerModel;
use Stripe\Exception\ApiErrorException;
use Stripe\Service\CustomerService;

class Customers extends Repositories
{
    /**
     * @var array
     */
    protected array $allowedParameters = [
        'address',
        'description',
        'email',
        'metadata',
        'name',
        'payment_method',
        'phone',
        'shipping',
        'cash_balance',
        'coupon',
        'invoice_prefix',
        'invoice_settings',
        'next_invoice_sequence',
        'preferred_locales',
        'promotion_code',
        'source',
        'tax',
        'tax_exempt',
        'tax_id_data',
        'test_clock'
    ];

    public function __construct(
        protected null|CustomerService $customers,
        protected readonly null|string $stripeAccount = null
    )
    {}

    /**
     * @return \Stripe\Collection
     * @throws ApiErrorException
     */
    public function all($params = []): \Stripe\Collection
    {
        $data =  $this->customers->all($this->getParams($params), $this->getOpts());
        $this->clearObjectParams();

        return $data;
    }

    /**
     * @param $params
     * @return \Stripe\SearchResult
     * @throws ApiErrorException
     */
    public function search($params = []): \Stripe\SearchResult
    {
        $data = $this->customers->search($this->getParams($params), $this->getOpts());
        $this->clearObjectParams();

        return $data;
    }

    /**
     * @param $customerId
     * @return \Stripe\Customer
     * @throws ApiErrorException
     */
    public function retrieve($customerId): \Stripe\Customer
    {
        $this->getCustomerIdByUser($customerId);
        $data = $this->customers->retrieve($customerId, $this->getParams(), $this->getOpts());
        $this->clearObjectParams();

        return $data;
    }

    /**
     * @param $user
     * @param $token
     * @return \Stripe\Customer
     * @throws ApiErrorException
     */
    public function create($user, $token = null): \Stripe\Customer
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

        $customerObject = $this->customers->create($this->getParams(), $this->getOpts());
        $this->createCustomerModel($user, $customerObject);
        $this->clearObjectParams();
        return $customerObject;
    }

    /**
     * @param $customerId
     * @param $params
     * @return \Stripe\Customer
     * @throws ApiErrorException
     */
    public function update($customerId, $params): \Stripe\Customer
    {
        $this->getCustomerIdByUser($customerId);
        $data = $this->customers->update($customerId, $this->getParams($params), $this->getOpts());
        $this->clearObjectParams();

        return $data;
    }

    /**
     * @param $customerId
     * @return \Stripe\Customer
     * @throws ApiErrorException
     */
    public function delete($customerId): \Stripe\Customer
    {
        $this->getCustomerIdByUser($customerId);
        $data = $this->customers->delete($customerId, $this->getParams(), $this->getOpts());
        $this->clearObjectParams();

        return $data;
    }

    /**
     * @param $customerId
     * @param $params
     * @return \Stripe\Collection
     * @throws ApiErrorException
     */
    public function paymentMethods($customerId, $params = null): \Stripe\Collection
    {
        $this->getCustomerIdByUser($customerId);
        $data = $this->customers->allPaymentMethods($customerId, $this->getParams($params), $this->getOpts());
        $this->clearObjectParams();

        return $data;
    }

    /**
     * @param $customerId
     * @param $paymentMethodId
     * @return \Stripe\PaymentMethod
     * @throws ApiErrorException
     */
    public function retrievePaymentMethod($customerId, $paymentMethodId): \Stripe\PaymentMethod
    {
        $this->getCustomerIdByUser($customerId);
        $data = $this->customers->retrievePaymentMethod($customerId, $paymentMethodId, $this->getParams(), $this->getOpts());
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



            $this->setParam($param, $val);
        }
    }

    /**
     * @param $user
     * @param \Stripe\Customer $object
     * @return bool|StripeCustomerModel
     */
    protected function createCustomerModel($user, \Stripe\Customer $object)
    {
        $userForStripe = $this->getCustomerModel($user, true);

        if (!$userForStripe->stripe_customer_id) {
            $userForStripe->stripe_customer_id = $object->id;
            $userForStripe->save();
        }

        return $userForStripe;
    }
}