<?php

namespace Dominservice\LaraStripe\Helpers;

use Dominservice\LaraStripe\Exception\ParameterBadValueException;
use Illuminate\Support\Arr;

class ValidateHelper
{
    /**
     * @param $metadata
     * @return void
     * @throws ParameterBadValueException
     */
    public static function metadata($metadata): void
    {
        $isFail = false;

        if (!is_array($metadata)) {
            $isFail = true;
        } elseif (Arr::isList($metadata)) {
            $isFail = true;
        }

        foreach ($metadata as $key => $val) {
            if (is_array($val) || is_object($val)) {
                $isFail = true;
                break;
            }
        }

        if ($isFail) {
            throw new ParameterBadValueException("The 'metadata' parameter has an invalid value. Set of key-value pairs that you can attach to an object.");
        }
    }

    /**
     * @param $key
     * @param $val
     * @return void
     * @throws ParameterBadValueException
     */
    public static function boolValue($key, &$val): void
    {
        if ((!in_array($val,[0,1]) || !is_bool($val))) {
            throw new ParameterBadValueException("The '{$key}' parameter has an invalid value. The correct value should be one of 0, 1, false, true.");
        }

        $val = (bool)$val;
    }

    /**
     * @param $currency
     * @return void
     * @throws ParameterBadValueException
     */
    public static function currency(&$currency): void
    {
        if (!is_string($currency) || strlen($currency) !== 3) {
            throw new ParameterBadValueException("The 'statement_descriptor' parameter has an invalid value. Check the documentation: https://docs.stripe.com/currencies");
        }

        if (in_array($currency, config('stripe.currencies'))) {
            throw new ParameterBadValueException("The currency must be declared in the package configuration.");
        }

        $currency = mb_strtolower($currency);
    }

    /**
     * @param $options
     * @return void
     * @throws ParameterBadValueException
     */
    public static function currencyOptions($options): void
    {
        $isFail = false;

        foreach ($options as $currency => $option) {
            if (strlen($currency) !== 3
                || (isset($option['custom_unit_amount']) && !isset($option['custom_unit_amount']['enabled']))
            ){
                $isFail = true;
            }
        }

        if ($isFail) {
            throw new ParameterBadValueException("The 'currency_options' parameter has an invalid value. Check the documentation: https://stripe.com/docs/api/prices/create#create_price-currency_options");
        }
    }

    /**
     * @param $val
     * @return void
     * @throws ParameterBadValueException
     */
    public static function features($val): void
    {
        $isFail = false;

        if (!is_array($val)) {
            $isFail = true;
        } elseif (count($val) > 15) {
            $isFail = true;
        } elseif (Arr::isAssoc($val)) {
            $isFail = true;
        } else {
            foreach ($val as $item) {
                if (!is_array($item)
                    || (is_array($item)
                        && (count($item) > 1
                            || !isset($item['name']) || strlen($item['name']) > 80))
                ) {
                    $isFail = true;
                }
            }
        }

        if ($isFail) {
            throw new ParameterBadValueException("The 'features' parameter has an invalid value. Check the documentation: https://stripe.com/docs/api/products/create#create_product-features.");
        }
    }

    /**
     * @param $val
     * @return void
     * @throws ParameterBadValueException
     */
    public static function images($val): void
    {
        if (!is_array($val) || (is_array($val) && Arr::isAssoc($val))) {
            throw new ParameterBadValueException("The 'images' parameter has an invalid value. A list of up to 8 URLs of images for this product, meant to be displayable to the customer.");
        }

        foreach ($val as $item) {
            if (is_array($item)) {
                throw new ParameterBadValueException("The 'images' parameter has an invalid value. A list of up to 8 URLs of images for this product, meant to be displayable to the customer.");
            }
        }
    }

    /**
     * @param $val
     * @return void
     * @throws ParameterBadValueException
     */
    public static function dimensions($val): void
    {
        if (!is_array($val)
            || (is_array($val) && (Arr::isList($val)
                    || !isset($val['height'])
                    || !isset($val['length'])
                    || !isset($val['weight'])
                    || !isset($val['width']) ))
        ) {
            throw new ParameterBadValueException("The 'package_dimensions' parameter has an invalid value. Check the documentation: https://stripe.com/docs/api/products/create#create_product-package_dimensions");
        }
    }

    /**
     * @param string $val
     * @return void
     * @throws ParameterBadValueException
     */
    public static function recurring($val)
    {
        if (!is_array($val)
            || (is_array($val) && (Arr::isList($val)
                    || (!isset($val['interval']) || !in_array($val['interval'], ['day', 'month', 'week', 'year']))
                    || (isset($val['aggregate_usage']) && !in_array($val['aggregate_usage'], ['last_during_period', 'last_ever', 'max', 'sum']))
                    || (isset($val['interval_count']) && !is_int($val['interval_count']))
                    || (isset($val['usage_type']) && !in_array($val['usage_type'], ['licensed', 'metered']))
                ))
        ) {
            throw new ParameterBadValueException("The 'recurring' parameter has an invalid value. Check the documentation: https://stripe.com/docs/api/prices/create#create_price-recurring");
        }
    }

    /**
     * @param $val
     * @return void
     * @throws ParameterBadValueException
     */
    public static function customUnitAmount($val)
    {
        if (!is_array($val) || (is_array($val) && (Arr::isList($val) || !isset($val['enabled'])))) {
            throw new ParameterBadValueException("The 'custom_unit_amount' parameter has an invalid value. Check the documentation: https://stripe.com/docs/api/prices/create#create_price-custom_unit_amount");
        }
    }

    public static function transformQuantity($val)
    {
        if (!is_array($val) || (is_array($val) && (Arr::isList($val) || !isset($val['divide_by']) || !isset($val['round'])))) {
            throw new ParameterBadValueException("The 'transform_quantity' parameter has an invalid value. Check the documentation: https://stripe.com/docs/api/prices/create#create_price-transform_quantity");
        }
    }

    public static function addInvoiceItems(string $val)
    {
        if (!is_array($val)
            || (is_array($val) && (Arr::isAssoc($val) || count($val) > 20))
        ) {
            throw new ParameterBadValueException("The 'add_invoice_items' parameter has an invalid value. Check the documentation: https://docs.stripe.com/api/subscriptions/create#create_subscription-add_invoice_items");
        }

        foreach ($val as $item) {
            if (!is_array($item)
                || (is_array($item)
                    && (Arr::isList($item)
                        || (isset($item['price_data']) && (!isset($item['price_data']['currency'])
                                || !isset($item['price_data']['product'])))))
            ) {
                throw new ParameterBadValueException("The 'add_invoice_items' parameter has an invalid value. Check the documentation: https://docs.stripe.com/api/subscriptions/create#create_subscription-add_invoice_items");
            }
        }
    }
}