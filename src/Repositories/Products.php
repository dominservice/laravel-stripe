<?php

namespace Dominservice\LaraStripe\Repositories;

use Dominservice\LaraStripe\Exception\ParameterBadValueException;
use Dominservice\LaraStripe\Helpers\ValidateHelper;
use Dominservice\LaraStripe\Models\StripeProduct as StripeProductModel;
use Illuminate\Support\Str;
use Stripe\Exception\ApiErrorException;
use Stripe\Service\PriceService;
use Stripe\Service\ProductService;

class Products extends Repositories
{
    private null|Prices $pricesRepository = null;

    protected array $allowedParameters = [
        'name',
        'active',
        'description',
        'id',
        'metadata',
        'default_price_data',
        'default_price',
        'features',
        'images',
        'package_dimensions',
        'shippable',
        'statement_descriptor',
        'tax_code',
        'unit_label',
        'url',
    ];

    protected $modelClass = StripeProductModel::class;

    protected $modelObjectKey = 'stripe_product_id';

    protected array $extendObjects = [
        'extend_prices' => [
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
        ],
    ];
    private null|\Stripe\Price $priceDefault = null;

    public function __construct(
        protected null|ProductService  $products,
        protected null|PriceService    $prices,
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
        $data = $this->products->all($this->getParams($params), $this->getOpts());
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
        $this->object = $this->products->search($this->getParams($params), $this->getOpts());
        $this->clearObjectParams();

        return $this->object;
    }

    /**
     * @param $productId
     * @return \Stripe\Product|null
     * @throws ApiErrorException
     */
    public function retrieve($productId): \Stripe\Product|null
    {
        $this->getProductIdByParent($productId);

        if ($productId !== $this->_empty) {
            $this->object = $this->products->retrieve($productId, $this->getParams(), $this->getOpts());
        }

        $this->clearObjectParams();

        return $this->object;
    }

    /**
     * @param $parent
     * @return \Stripe\Product|null
     * @throws ApiErrorException
     * @throws ParameterBadValueException
     * @throws \Dominservice\LaraStripe\Exception\NoParametersException
     */
    public function create($parent): \Stripe\Product|null
    {
        if (empty($this->params['name'])) {
            throw new \Dominservice\LaraStripe\Exception\NoParametersException("The 'name' parameter is required, you must provide this parameter to properly create the product.");
        }

        $this->object = $this->products->create($this->getParams(), $this->getOpts());
        $this->createProductModel($parent, $this->object);

        if (!empty($this->extendObjectsParam['extend_prices'])) {
            $prices = $this->prices($this->object, true);
            $prices->setParam($this->extendObjectsParam['extend_prices']);
            $this->priceDefault = $prices->create($this->object, $this->model, true);
            $this->clearObjectParams();
            $this->expandDefaultPrice();
            $this->object = $this->products->update(
                $this->object->id,
                $this->getParams(['default_price' => $this->priceDefault->id]),
                $this->getOpts()
            );
        }

        $this->clearObjectParams();

        return $this->object;
    }

    /**
     * @param $productId
     * @return \Stripe\Product|null
     * @throws ApiErrorException
     */
    public function update($productId): \Stripe\Product|null
    {
        $this->getProductIdByParent($productId);

        $this->object = $this->products->update($productId, $this->getParams(), $this->getOpts());
        $this->clearObjectParams();

        return $this->object;
    }

    /**
     * @param $productId
     * @return \Stripe\Product|null
     * @throws ApiErrorException
     */
    public function delete($productId): \Stripe\Product|null
    {
        $this->getProductIdByParent($productId);
        $this->object = $this->products->delete($productId, $this->getParams(), $this->getOpts());
        $this->clearObjectParams();

        return $this->object;
    }

    /**
     * @param \Stripe\Product|null $product
     * @param $force
     * @return Prices
     */
    public function prices(null|\Stripe\Product $product = null, $force = false): Prices
    {
        if (!$this->pricesRepository || $force) {
            $this->pricesRepository = new Prices($this->prices, $product ?? $this->object, $this->stripeAccount);
        }

        return $this->pricesRepository;
    }

    public function getStripeProductDefaultPrice()
    {
        if (!$this->priceDefault && $productObject = $this->getStripeObject()) {
//            $this->priceDefault = '';
        }

        return $this->priceDefault;
    }

    /**
     * @return $this
     */
    public function expandDefaultPrice()
    {
        $this->setExpand('default_price');

        return $this;
    }

    /**
     * @return $this
     */
    public function expandAllPrices()
    {
        $this->setExpand('prices');

        return $this;
    }

    /**
     * @return $this
     */
    public function expandTaxCode()
    {
        $this->setExpand('tax_code');

        return $this;
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
            if ($param === 'extend_prices') {
                if ($key === 'currency') {
                    ValidateHelper::currency($val);
                } elseif ($key === 'active') {
                    ValidateHelper::boolValue($key, $val);
                } elseif ($key === 'metadata') {
                    ValidateHelper::metadata($val);
                } elseif ($key === 'product' && !is_string($val)) {
                    throw new ParameterBadValueException("The 'product' parameter has an invalid value. You must provide a product ID previously created in Stripe.");
                } elseif ($key === 'recurring') {
                    ValidateHelper::recurring($val);
                } elseif ($key === 'billing_scheme' && !in_array($val, ['per_unit', 'tiered'])) {
                    throw new \Dominservice\LaraStripe\Exception\NoParametersException("If 'billing_scheme=tiered' you must provide the 'per_unit' or 'tiered' parameter.");
                } elseif ($key === 'currency_options') {
                    ValidateHelper::currencyOptions($val);
                } elseif ($key === 'custom_unit_amount') {
                    ValidateHelper::customUnitAmount($val);
                } elseif ($key === 'product_data') {
                    throw new ParameterBadValueException("This package does not support the 'product_data' parameter. To add a product, you must add it via \Dominservice\LaraStripe\Repositories\Products::class. Once the product is added, provide the 'product' parameter and the product ID.");
                } elseif ($key === 'transform_quantity') {
                    ValidateHelper::transformQuantity($val);
                }
            }

            $this->setExtendObjectsParam($param, $key, $val);
        } else {
            $val = $key;

            if (in_array($param, ['active', 'shippable'])) {
                ValidateHelper::boolValue($param, $val);
            } elseif ($param === 'metadata') {
                ValidateHelper::metadata($val);
            } elseif ($param === 'features') {
                ValidateHelper::features($val);
            } elseif ($param === 'default_price_data') {
                throw new ParameterBadValueException("This package does not support the 'default_price_data' parameter. To add a base price, you need to handle the setExtendPrices{NamePriceParameter}() method. An example call to add currency for CEMA: setExtendPricesCurrency('PLN').");
            } elseif ($param === 'images') {
                ValidateHelper::images($val);
            } elseif ($param === 'package_dimensions') {
                ValidateHelper::dimensions($val);
            } elseif ($param === 'statement_descriptor'
                && (!is_string($val) || strlen($val) < 1 || strlen($val) > 22 || preg_match('#(<|>|\\|"|\')#', $val))
            ) {
                throw new ParameterBadValueException("The 'statement_descriptor' parameter has an invalid value. Check the documentation: https://stripe.com/docs/api/products/create#create_product-statement_descriptor");
            }

            $this->setParam($param, $val);
        }
    }

    public function getProductModel($parent, $emptyModel = false)
    {
        $parentKey = $parent->getKeyName() != 'id' ? $parent->getKeyName() . '_parent_id' : 'parent_id';
        $this->model = StripeProductModel::where($parentKey, $parent->{$parent->getKeyName()})->first();

        if (!$this->model && $emptyModel) {
            $this->model = new StripeProductModel();
            $relationType = $parent->getMorphClass();
            $relationId = $parent->{$parent->getKeyName()};

            if (Str::isUlid($relationId)) {
                $this->model->ulid_parent_type = $relationType;
            } elseif(Str::isUuid($relationId)) {
                $this->model->uuid_parent_type = $relationType;
            } else {
                $this->model->parent_type = $relationType;
            }

            $this->model->$parentKey = $relationId;
            $this->model->save();
        }

        return $this->model;
    }

    /**
     * @param $parent
     * @param \Stripe\Product $product
     * @return StripeProductModel|bool
     */
    protected function createProductModel($parent, \Stripe\Product $product): StripeProductModel|null
    {
        $this->getProductModel($parent, true);

        if (!$this->model->stripe_product_id) {
            $this->model->stripe_product_id = $product->id;
            $this->model->name = $product->name;
            $this->model->status = (int)$product->active;
            $this->model->save();
        }

        return $this->model;
    }

    /**
     * @param $parent
     * @return void
     */
    private function getProductIdByParent(&$parent): void
    {
        if (is_string($parent)) {
            $parent = $parent;
        } elseif ($productStripe = $this->getProductModel($parent)) {
            $parent = $productStripe->stripe_product_id;
        } else {
            $parent = $this->_empty;
        }
    }
}
