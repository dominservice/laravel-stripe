<?php

namespace Dominservice\LaraStripe\Repositories;

use Dominservice\LaraStripe\Exception\ParameterBadValueException;
use Dominservice\LaraStripe\Helpers\ValidateHelper;
use Dominservice\LaraStripe\Models\StripeInvoice as StripeInvoiceModel;
use Stripe\Exception\ApiErrorException;
use Stripe\Service\InvoiceService;

class Invoices extends Repositories
{
    /**
     * @var array
     */
    protected array $allowedParameters = [

    ];

    protected $modelClass = StripeInvoiceModel::class;

    protected $modelObjectKey = 'stripe_invoice_id';

    public function __construct(
        protected null|InvoiceService $invoices,
        protected readonly null|string $stripeAccount = null
    )
    {}

    /**
     * @param $params
     * @return \Stripe\Collection
     * @throws ApiErrorException
     */
    public function all(): \Stripe\Collection
    {
        $data = $this->invoices->all($this->getParams(), $this->getOpts());
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
        $data = $this->invoices->search($this->getParams($params), $this->getOpts());
        $this->clearObjectParams();

        return $data;
    }

    /**
     * @param $subscriptionId
     * @return \Stripe\Invoice
     * @throws ApiErrorException
     */
    public function retrieve($invoiceId): \Stripe\Invoice
    {
        $this->object = $this->invoices->retrieve($invoiceId, $this->getParams(), $this->getOpts());
        $this->clearObjectParams();

        return $this->object;
    }

    /**
     * @param $subscriptionId
     * @return \Stripe\Collection
     * @throws ApiErrorException
     */
    public function allLines($invoiceId): \Stripe\Collection
    {
        $data = $this->invoices->allLines($invoiceId, $this->getParams(), $this->getOpts());
        $this->clearObjectParams();

        return $data;
    }

    /**
     * @param $customerId
     * @return \Stripe\Invoice
     * @throws ApiErrorException
     * @throws \Dominservice\LaraStripe\Exception\NoParametersException
     */
    public function create($customerId): \Stripe\Invoice
    {
//        $prices = [];
//
//        if (empty($this->params['customer'])) {
//            throw new \Dominservice\LaraStripe\Exception\NoParametersException("The 'customer' parameter is required, you must provide this parameter to properly create the subscription.");
//        }
//
//        if (empty($this->params['items'])) {
//            throw new \Dominservice\LaraStripe\Exception\NoParametersException("The 'items' parameter is required, you must provide this parameter to properly create the subscription.");
//        } else {
//            foreach ($this->params['items'] as $item) {
//                if (empty($item['price'])) {
//                    throw new \Dominservice\LaraStripe\Exception\NoParametersException("The 'items.[].price' parameter is required");
//                } else {
//                    $prices[] = $item['price'];
//                }
//            }
//        }
//
//        if (isset($this->params['payment_behavior'])
//            && (!isset($this->params['collection_method'])
//                || (isset($this->params['collection_method']) && $this->params['collection_method'] !== 'charge_automatically'))
//        ) {
//            throw new \Dominservice\LaraStripe\Exception\NoParametersException("The 'payment_behavior' parameter is only applies to subscriptions with collection_method=charge_automatically. Check the documentation: https://docs.stripe.com/api/subscriptions/create#create_subscription-payment_behavior");
//        }
//
//        $this->getCustomerIdByUser($customerId);
//        $this->object = $this->invoices->create($this->getParams(), $this->getOpts());
//        $this->createSubscriptionModel($customerId, $this->object, $prices);
//        $this->clearObjectParams();
//
//        return $this->object;
    }

    /**
     * @param $invoiceId
     * @return \Stripe\Invoice
     * @throws ApiErrorException
     */
    public function update($invoiceId): \Stripe\Invoice
    {
        $this->object = $this->invoices->update($invoiceId, $this->getParams(), $this->getOpts());
        $this->clearObjectParams();

        return $this->object;
    }

    /**
     * @param $invoiceId
     * @return \Stripe\Invoice|null
     * @throws ApiErrorException
     */
    public function delete($invoiceId): \Stripe\Invoice|null
    {
        $this->object = $this->invoices->delete($invoiceId, $this->getParams(), $this->getOpts());
        $this->clearObjectParams();

        return $this->object;
    }

    /**
     * @param $invoiceId
     * @return \Stripe\Invoice
     * @throws ApiErrorException
     */
    public function finalizeInvoice($invoiceId): \Stripe\Invoice
    {
        $this->object = $this->invoices->finalizeInvoice($invoiceId, $this->getParams(), $this->getOpts());
        $this->clearObjectParams();

        return $this->object;
    }

    /**
     * @param $invoiceId
     * @return \Stripe\Invoice
     * @throws ApiErrorException
     */
    public function markUncollectible($invoiceId): \Stripe\Invoice
    {
        $this->object = $this->invoices->markUncollectible($invoiceId, $this->getParams(), $this->getOpts());
        $this->clearObjectParams();

        return $this->object;
    }

    /**
     * @param $invoiceId
     * @return \Stripe\Invoice
     * @throws ApiErrorException
     */
    public function pay($invoiceId): \Stripe\Invoice
    {
        $this->object = $this->invoices->pay($invoiceId, $this->getParams(), $this->getOpts());
        $this->clearObjectParams();

        return $this->object;
    }

    /**
     * @param $invoiceId
     * @return \Stripe\Invoice
     * @throws ApiErrorException
     */
    public function sendInvoice($invoiceId): \Stripe\Invoice
    {
        $this->object = $this->invoices->sendInvoice($invoiceId, $this->getParams(), $this->getOpts());
        $this->clearObjectParams();

        return $this->object;
    }

    /**
     * @return \Stripe\Invoice
     * @throws ApiErrorException
     */
    public function upcoming(): \Stripe\Invoice
    {
        $this->object = $this->invoices->upcoming($this->getParams(), $this->getOpts());
        $this->clearObjectParams();

        return $this->object;
    }

    /**
     * @return \Stripe\Collection
     * @throws ApiErrorException
     */
    public function upcomingLines(): \Stripe\Collection
    {
        $this->object = $this->invoices->upcomingLines($this->getParams(), $this->getOpts());
        $this->clearObjectParams();

        return $this->object;
    }

    /**
     * @param $invoiceId
     * @return \Stripe\Invoice
     * @throws ApiErrorException
     */
    public function voidInvoice($invoiceId): \Stripe\Invoice
    {
        $this->object = $this->invoices->voidInvoice($invoiceId, $this->getParams(), $this->getOpts());
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

            if ($param === 'customer' && !is_string($val)) {
                throw new ParameterBadValueException("The 'customer' parameter has an invalid value. You must provide a customer ID previously created in Stripe.");
            } elseif ($param === 'currency') {
                ValidateHelper::currency($val);
            } elseif ($param === 'metadata') {
                ValidateHelper::metadata($val);
            } elseif ($param === 'application_fee_amount' && !isset($this->stripeAccount)) {
                throw new ParameterBadValueException("The 'application_fee_amount' parameter is used exclusively for Stripe Connect");
            } elseif ($param === 'issuer' && !isset($this->stripeAccount)) {
                throw new ParameterBadValueException("The 'issuer' parameter is used exclusively for Stripe Connect");
            } elseif ($param === 'on_behalf_of' && !isset($this->stripeAccount)) {
                throw new ParameterBadValueException("The 'on_behalf_of' parameter is used exclusively for Stripe Connect");
            } elseif ($param === 'transfer_data' && !isset($this->stripeAccount)) {
                throw new ParameterBadValueException("The 'transfer_data' parameter is used exclusively for Stripe Connect");
            }

            $this->setParam($param, $val);
        }
    }

}