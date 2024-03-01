<?php

namespace Dominservice\LaraStripe\Repositories;

use Dominservice\LaraStripe\Exception\ParameterBadValueException;
use Dominservice\LaraStripe\Helpers\ValidateHelper;
use Dominservice\LaraStripe\Models\StripeSubscription as StripeSubscriptionModel;
use Illuminate\Support\Arr;
use Stripe\Exception\ApiErrorException;
use Stripe\Service\SubscriptionService;

class Subscriptions extends Repositories
{
    /**
     * @var array
     */
    protected array $allowedParameters = [
        'customer',
        'cancel_at_period_end',
        'default_payment_method',
        'description',
        'items',
        'metadata',
        'payment_behavior',
        'add_invoice_items',
        'application_fee_percent',
        'automatic_tax',
        'backdate_start_date',
        'billing_cycle_anchor',
        'billing_cycle_anchor_config',
        'billing_thresholds',
        'cancel_at',
        'collection_method',
        'coupon',
        'days_until_due',
        'default_source',
        'default_tax_rates',
        'invoice_settings',
        'off_session',
        'on_behalf_of',
        'payment_settings',
        'pending_invoice_item_interval',
        'promotion_code',
        'proration_behavior',
        'transfer_data',
        'trial_end',
        'trial_from_plan',
        'trial_period_days',
        'trial_settings',
    ];

    protected $checkoutSessionId;

    protected $modelClass = StripeSubscriptionModel::class;

    protected $modelObjectKey = 'stripe_subscription_id';

    public function __construct(
        protected null|SubscriptionService $subscriptions,
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
        $data = $this->subscriptions->all($this->getParams($params), $this->getOpts());
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
        $data = $this->subscriptions->search($this->getParams($params), $this->getOpts());
        $this->clearObjectParams();

        return $data;
    }

    /**
     * @param $subscriptionId
     * @return \Stripe\Subscription
     * @throws ApiErrorException
     */
    public function retrieve($subscriptionId): \Stripe\Subscription
    {
        $data = $this->subscriptions->retrieve($subscriptionId, $this->getParams(), $this->getOpts());
        $this->clearObjectParams();

        return $data;
    }

    /**
     * @param $customerId
     * @return \Stripe\Subscription
     * @throws ApiErrorException
     * @throws \Dominservice\LaraStripe\Exception\NoParametersException
     */
    public function create($customerId): \Stripe\Subscription
    {
        $prices = [];

        if (empty($this->params['customer'])) {
            throw new \Dominservice\LaraStripe\Exception\NoParametersException("The 'customer' parameter is required, you must provide this parameter to properly create the subscription.");
        }

        if (empty($this->params['items'])) {
            throw new \Dominservice\LaraStripe\Exception\NoParametersException("The 'items' parameter is required, you must provide this parameter to properly create the subscription.");
        } else {
            foreach ($this->params['items'] as $item) {
                if (empty($item['price'])) {
                    throw new \Dominservice\LaraStripe\Exception\NoParametersException("The 'items.[].price' parameter is required");
                } else {
                    $prices[] = $item['price'];
                }
            }
        }

        if (isset($this->params['payment_behavior'])
            && (!isset($this->params['collection_method'])
                || (isset($this->params['collection_method']) && $this->params['collection_method'] !== 'charge_automatically'))
        ) {
            throw new \Dominservice\LaraStripe\Exception\NoParametersException("The 'payment_behavior' parameter is only applies to subscriptions with collection_method=charge_automatically. Check the documentation: https://docs.stripe.com/api/subscriptions/create#create_subscription-payment_behavior");
        }

        $this->getCustomerIdByUser($customerId);
        $this->object = $this->subscriptions->create($this->getParams(), $this->getOpts());
        $this->createSubscriptionModel($customerId, $this->object, $prices);
        $this->clearObjectParams();

        return $this->object;
    }

    /**
     * @param $subscriptionId
     * @param $params
     * @return \Stripe\Subscription
     * @throws ApiErrorException
     */
    public function update($subscriptionId): \Stripe\Subscription
    {
        $this->object = $this->subscriptions->update($subscriptionId, $this->getParams(), $this->getOpts());
        $this->clearObjectParams();

        return $this->object;
    }

    /**
     * @param $subscriptionId
     * @return \Stripe\Subscription
     * @throws ApiErrorException
     */
    public function resume($subscriptionId): \Stripe\Subscription
    {
        $this->object = $this->subscriptions->resume($subscriptionId, $this->getParams(), $this->getOpts());
        $this->clearObjectParams();

        return $this->object;
    }

    /**
     * @param $subscriptionId
     * @return \Stripe\Subscription
     * @throws ApiErrorException
     */
    public function cancel($subscriptionId): \Stripe\Subscription
    {
        $this->object = $this->subscriptions->cancel($subscriptionId, $this->getParams(), $this->getOpts());
        $this->clearObjectParams();

        return $this->object;
    }

    /**
     * @param $subscriptionId
     * @return \Stripe\Discount
     * @throws ApiErrorException
     */
    public function deleteDiscount($subscriptionId): \Stripe\Discount
    {
        $data = $this->subscriptions->deleteDiscount($subscriptionId, $this->getParams(), $this->getOpts());
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

            if ($param === 'customer' && !is_string($val)) {
                throw new ParameterBadValueException("The 'customer' parameter has an invalid value. You must provide a customer ID previously created in Stripe.");
            } elseif ($param === 'cancel_at_period_end') {
                ValidateHelper::boolValue($param, $val);
            } elseif ($param === 'currency') {
                ValidateHelper::currency($val);
            } elseif ($param === 'items' && is_string($val)) {
                throw new ParameterBadValueException("The 'items' parameter has an invalid value. Check the documentation: https://docs.stripe.com/api/subscriptions/create#create_subscription-items");
            } elseif ($param === 'metadata') {
                ValidateHelper::metadata($val);
            } elseif ($param === 'payment_behavior' && (!is_string($val)
                || (is_string($val) && !in_array($val, ['allow_incomplete', 'default_incomplete', 'error_if_incomplete', 'pending_if_incomplete'])))
            ) {
                throw new ParameterBadValueException("The 'payment_behavior' parameter has an invalid value. Check the documentation: https://docs.stripe.com/api/subscriptions/create#create_subscription-payment_behavior");
            } elseif ($param === 'add_invoice_items') {
                ValidateHelper::addInvoiceItems($val);
            } elseif ($param === 'application_fee_percent' && !isset($this->stripeAccount)) {
                throw new ParameterBadValueException("The 'application_fee_percent' parameter is used exclusively for Stripe Connect");
            } elseif ($param === 'automatic_tax'
                && (!is_array($val) || (is_array($val) && (Arr::isList($val) || !isset($val['enabled']))))
            ) {
                throw new ParameterBadValueException("The 'automatic_tax' parameter has an invalid value. Param automatic_tax.enabled is required.");
            }

            $this->setParam($param, $val);
        }
    }

    /**
     * @param $subscriptionId
     * @param bool $emptyModel
     * @return StripeSubscriptionModel|null
     */
    protected function getSubscriptionModel($subscriptionId, bool $emptyModel = false): StripeSubscriptionModel|null
    {

        if (is_a($subscriptionId, \Stripe\Subscription::class)) {
            $subscriptionId = $subscriptionId->id;
        }

        $this->model = StripeSubscriptionModel::where('stripe_subscription_id', $subscriptionId)->first();

        if (!$this->model && $emptyModel) {
            $this->model = new StripeSubscriptionModel();
            $this->model->stripe_subscription_id = $subscriptionId;

            if (!empty($this->checkoutSessionId)) {
                $this->model->stripe_checkout_session_id = $this->checkoutSessionId;
            }

            $this->model->save();
        }

        return $this->model;
    }

    /**
     * @param $user
     * @param \Stripe\Subscription $object
     * @param string $priceId
     * @return StripeSubscriptionModel|bool
     */
    public function createSubscriptionModel($user, \Stripe\Subscription $object, string|array $prices): StripeSubscriptionModel|bool
    {
        $this->getSubscriptionModel($object, true);

        if ($this->model && !$this->model->customer_id && !$this->model->price_id) {
            $customer = $this->getCustomerModel($user);
            $this->model->customer_id = $customer->id;
            $this->model->save();
            $this->model->syncPrice($prices);
        }

        return $this->model;
    }

    public function setCheckoutSessionId($id)
    {
        $this->checkoutSessionId = $id;

        return $this;
    }

}
