<?php

namespace Dominservice\LaraStripe\Repositories;

use Dominservice\LaraStripe\Exception\ParameterBadValueException;
use Dominservice\LaraStripe\Helpers\ValidateHelper;
use Stripe\Exception\ApiErrorException;
use Stripe\Service\Checkout\SessionService;

class CheckoutSession extends Repositories
{
    /**
     * @var array
     */
    protected array $allowedParameters = [
        'client_reference_id',
        'customer',
        'customer_email',
        'line_items',
        'metadata',
        'mode',
        'return_url',
        'success_url',
        'after_expiration',
        'allow_promotion_codes',
        'automatic_tax',
        'billing_address_collection',
        'cancel_url',
        'consent_collection',
        'currency',
        'custom_fields',
        'custom_text',
        'customer_creation',
        'customer_update',
        'discounts',
        'expires_at',
        'invoice_creation',
        'locale',
        'payment_intent_data',
        'payment_method_collection',
        'payment_method_configuration',
        'payment_method_options',
        'payment_method_types',
        'phone_number_collection',
        'redirect_on_completion',
        'setup_intent_data',
        'shipping_address_collection',
        'shipping_options',
        'submit_type',
        'subscription_data',
        'tax_id_collection',
        'ui_mode',
    ];

    /**
     * @param SessionService|null $session
     * @param string|null $stripeAccount
     */
    public function __construct(
        protected null|SessionService $session,
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
        $data = $this->session->all($this->getParams($params), $this->getOpts());
        $this->clearObjectParams();

        return $data;
    }

    /**
     * @param $sessionId
     * @return \Stripe\Checkout\Session
     * @throws ApiErrorException
     */
    public function retrieve($sessionId): \Stripe\Checkout\Session
    {
        $data = $this->session->retrieve($sessionId, $this->getOpts());
        $this->clearObjectParams();

        return $data;
    }

    /**
     * @return \Stripe\Checkout\Session
     * @throws ApiErrorException
     * @throws \Dominservice\LaraStripe\Exception\NoParametersException
     */
    public function create(): \Stripe\Checkout\Session
    {
        if (empty($this->params['mode'])) {
            throw new \Dominservice\LaraStripe\Exception\NoParametersException("The 'mode' parameter is required, should be one of 'payment', 'setup', 'subscription'.");
        }

        if ($this->params['mode'] !== 'setup' && !isset($this->params['line_items'])) {
            throw new \Dominservice\LaraStripe\Exception\NoParametersException("The 'line_items' parameter is required. Check the documentation: https://docs.stripe.com/api/checkout/sessions/create#create_checkout_session-line_items");
        }

        if ($this->params['mode'] === 'setup' && !isset($this->params['currency']) && !isset($this->params['payment_method_types'])) {
            throw new \Dominservice\LaraStripe\Exception\NoParametersException("The 'currency' parameter is required in setup mode when payment_method_types is not set. Check the documentation: https://docs.stripe.com/api/checkout/sessions/create#create_checkout_session-currency");
        }

        if (isset($this->params['ui_mode']) && $this->params['ui_mode'] === 'embedded') {
            if (!isset($this->params['return_url'])) {
                throw new \Dominservice\LaraStripe\Exception\NoParametersException("The 'return_url' parameter is required. Check the documentation: https://docs.stripe.com/api/checkout/sessions/create#create_checkout_session-return_url");
            }
            if (isset($this->params['success_url'])) {
                throw new \Dominservice\LaraStripe\Exception\NoParametersException("The 'success_url' parameter is not allowed if ui_mode is embedded. Check the documentation: https://docs.stripe.com/api/checkout/sessions/create#create_checkout_session-success_url");
            }
        }

        $this->object = $this->session->create($this->getParams(), $this->getOpts());
        $this->clearObjectParams();

        return $this->object;
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

            if ($param === 'line_items') {
                foreach ($val as $item) {
                    if (empty($item['price']) && empty($item['price_data'])) {
                        throw new ParameterBadValueException("The 'line_items' parameter has an invalid value. Check the documentation: https://docs.stripe.com/api/checkout/sessions/create#create_checkout_session-line_items");
                    }
                }
            } elseif ($param === 'metadata') {
                ValidateHelper::metadata($val);
            } elseif ($param === 'mode' && !in_array($val, ['payment', 'setup', 'subscription'])) {
                throw new ParameterBadValueException("The 'mode' parameter should be one of 'payment', 'setup', 'subscription'.");
            } elseif ($param === 'currency') {
                ValidateHelper::currency($val);
            }

            $this->setParam($param, $val);
        }
    }
}
