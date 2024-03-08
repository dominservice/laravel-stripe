<?php

namespace Dominservice\LaraStripe\Repositories;

use Dominservice\LaraStripe\Client as StripeClient;
use Dominservice\LaraStripe\Exception\ParameterBadValueException;
use Dominservice\LaraStripe\Helpers\PaymentHelper;
use Dominservice\LaraStripe\Helpers\ValidateHelper;
use Dominservice\LaraStripe\Models\StripePrice as StripePriceModel;
use Dominservice\LaraStripe\Models\StripeProduct as StripeProductModel;
use Stripe\Exception\ApiErrorException;
use Stripe\Service\PriceService;

class Prices extends Repositories
{
    protected array $allowedParameters = [
        'currency',
        'active',
        'metadata',
        'nickname',
        'product',
        'recurring',
        'unit_amount',
        'billing_scheme',
        'currency_options',
        'custom_unit_amount',
        'lookup_key',
        'product_data',
        'tax_behavior',
        'tiers',
        'tiers_mode',
        'transfer_lookup_key',
        'transform_quantity',
        'unit_amount_decimal',
    ];

    protected $modelClass = StripePriceModel::class;

    protected $modelObjectKey = 'stripe_price_id';

    private \Stripe\Price $price;

    public function __construct(
        protected null|PriceService $prices,
        protected null|\Stripe\Product $product = null,
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
        $data = $this->prices->all($this->getParams($params), $this->getOpts());
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
        $data = $this->prices->search($this->getParams($params), $this->getOpts());
        $this->clearObjectParams();

        return $data;
    }

    /**
     * @param $priceId
     * @return \Stripe\Price
     * @throws ApiErrorException
     */
    public function retrieve($priceId): \Stripe\Price
    {
        $this->object = $this->prices->retrieve($priceId, $this->getParams(), $this->getOpts());
        $this->clearObjectParams();

        return $this->object;
    }

    /**
     * @param string|\Stripe\Product $product
     * @param StripeProductModel $productModel
     * @param bool $isDefault
     * @return \Stripe\Price
     * @throws ApiErrorException
     * @throws ParameterBadValueException
     * @throws \Dominservice\LaraStripe\Exception\NoParametersException
     */
    public function create(string|\Stripe\Product|null $product, StripeProductModel $productModel, bool $isDefault = false): \Stripe\Price
    {
        if (empty($this->params['currency'])) {
            throw new \Dominservice\LaraStripe\Exception\NoParametersException("The 'currency' parameter is required, you must provide this parameter to properly create the product.");
        }

        if (!empty($this->params['billing_scheme']) && $this->params['billing_scheme'] === 'tiered') {
            if (empty($this->params['recurring'])) {
                throw new \Dominservice\LaraStripe\Exception\NoParametersException("If 'billing_scheme=tiered' you must provide the 'recurring' parameter.");
            }

            if (empty($this->params['unit_amount']) && empty($this->params['custom_unit_amount'])) {
                throw new \Dominservice\LaraStripe\Exception\NoParametersException("If 'billing_scheme=tiered' you must provide the 'unit_amount' or 'custom_unit_amount' parameter.");
            }

            if (isset($this->params['tiers']) && !isset($this->params['tiers']['up_to'])) {
                throw new \Dominservice\LaraStripe\Exception\NoParametersException("If 'billing_scheme=tiered' and 'tiers' exists you must provide the 'tiers.up_to' parameter.");
            }

            if (!isset($this->params['tiers_mode']) || !in_array($this->params['tiers_mode'], ['graduated', 'volume'])) {
                throw new \Dominservice\LaraStripe\Exception\NoParametersException("If 'billing_scheme=tiered'  exists you must provide the 'tiers_mode' parameter an value must be between 'graduated' and 'volume'.");
            }
        }

        if (!empty($this->params['currency_options'])) {
            foreach ($this->params['currency_options'] as $option) {
                if ((!empty($this->params['billing_scheme']) && $this->params['billing_scheme'] === 'tiered' && !isset($option['tiers']['up_to']))) {
                    throw new ParameterBadValueException("The 'currency_options' parameter has an invalid value. Check the documentation: https://stripe.com/docs/api/prices/create#create_price-currency_options");
                }
            }
        }

        if (isset($this->params['unit_amount'])) {
            $this->params['unit_amount'] = PaymentHelper::getValidAmount($this->params['currency'], $this->params['unit_amount']);
        }

        if ($product) {
            $this->setParam('product', is_string($product) ? $product : $product->id);
        } elseif ($tis->product) {
            $this->setParam('product', $tis->product->id);
        }

        $this->object = $this->prices->create($this->getParams(), $this->getOpts());
        $this->createPriceModel($productModel, $this->object, $isDefault);
        $this->clearObjectParams();

        return $this->object;
    }

    /**
     * @param $priceId
     * @param $params
     * @return \Stripe\Price
     * @throws ApiErrorException
     */
    public function update($priceId, $params): \Stripe\Price
    {
        $this->object = $this->prices->update($priceId, $this->getParams($params), $this->getOpts());
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

            if ($param === 'currency') {
                ValidateHelper::currency($val);
            } elseif ($param === 'active') {
                ValidateHelper::boolValue($param, $val);
            } elseif ($param === 'metadata') {
                ValidateHelper::metadata($val);
            } elseif ($param === 'product' && !is_string($val)) {
                throw new ParameterBadValueException("The 'product' parameter has an invalid value. You must provide a product ID previously created in Stripe.");
            } elseif ($param === 'recurring') {
                ValidateHelper::recurring($val);
            } elseif ($param === 'billing_scheme' && !in_array($val, ['per_unit', 'tiered'])) {
                throw new \Dominservice\LaraStripe\Exception\NoParametersException("If 'billing_scheme=tiered' you must provide the 'per_unit' or 'tiered' parameter.");
            } elseif ($param === 'currency_options') {
                ValidateHelper::currencyOptions($val);
            } elseif ($param === 'custom_unit_amount') {
                ValidateHelper::customUnitAmount($val);
            } elseif ($param === 'product_data') {
                throw new ParameterBadValueException("This package does not support the 'product_data' parameter. To add a product, you must add it via \Dominservice\LaraStripe\Repositories\Products::class. Once the product is added, provide the 'product' parameter and the product ID.");
            } elseif ($param === 'transform_quantity') {
                ValidateHelper::transformQuantity($val);
            }

            $this->setParam($param, $val);
        }
    }

    /**
     * @param $priceId
     * @param bool $emptyModel
     * @return StripePriceModel|bool
     */
    protected function getPriceModel($priceId, bool $emptyModel = false): StripePriceModel|null
    {
        if (!is_string($priceId)) {
            $priceId = $priceId->id;
        }

        $this->model = StripePriceModel::where('stripe_price_id', $priceId)->first();

        if (!$this->model && $emptyModel) {
            $this->model = new StripePriceModel();
            $this->model->stripe_price_id = $priceId;
            $this->model->save();
        }

        return $this->model;
    }

    /**
     * @param StripeProductModel $productModel
     * @param \Stripe\Price $object
     * @param bool $isDefault
     * @return void
     */
    private function createPriceModel(StripeProductModel $productModel, \Stripe\Price $object, bool $isDefault = false): void
    {
        $this->getPriceModel($object, true);

        if ($this->model && !$this->model->product_id) {
            $this->model->product_id = $productModel->id;
            $this->model->status = (int)$object->active;
            $this->model->price = (float)$object->unit_amount;
            $this->model->currency = $object->currency;
            $this->model->is_default = $isDefault;
            $this->model->save();

            if ($isDefault) {
                $stripe = (new StripeClient());
                $stripeProductRepository = $stripe->products();
                $this->product = $stripeProductRepository
                    ->setDefaultPrice($object->id)
                    ->update($productModel->stripe_product_id);
                
                StripePriceModel::where('product_id', $productModel->id)
                    ->where('stripe_price_id', '!=', $object->id)
                    ->update([
                        'is_default' => 0,
                    ]);
            }
        }
    }
}
