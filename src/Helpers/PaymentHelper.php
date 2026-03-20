<?php

namespace Dominservice\LaraStripe\Helpers;

use Dominservice\LaraStripe\Exception\ParameterBadValueException;

class PaymentHelper
{
    /**
     * @param $currency
     * @param $amount
     * @return float|int|mixed
     * @throws ParameterBadValueException
     */
    public static function getValidAmount($currency, $amount, $reverse = false): mixed
    {
        $currency = strtoupper(trim((string) $currency));
        $supportedCurrencies = (array) config('stripe.currencies', []);

        if (!in_array($currency, $supportedCurrencies, true)) {
            throw new ParameterBadValueException("The currency must be declared in the package configuration.");
        }

        if (in_array($currency, (array) config('stripe.three_decimal_currencies', []), true)) {
            $amount = $reverse ? $amount / 1000 : round(($amount * 1000), -1);
        } elseif (!in_array($currency, (array) config('stripe.zero_decimal_currencies', []), true)) {
            $amount = $reverse ? $amount / 100 : $amount * 100;
        }

        $minimumChargeAmounts = (array) config('stripe.minimum_charge_amounts', []);
        if (array_key_exists($currency, $minimumChargeAmounts)
            && (float) $amount < (float) $minimumChargeAmounts[$currency]
        ) {
            throw new ParameterBadValueException("The indicated amount is too low for this currency. Check the documentation: https://docs.stripe.com/currencies#minimum-and-maximum-charge-amounts");
        }

        return $amount;
    }

    public static function normalizeCurrencyCode($currency, bool $lowercase = false): string
    {
        $normalized = strtoupper(trim((string) $currency));

        if (!in_array($normalized, (array) config('stripe.currencies', []), true)) {
            throw new ParameterBadValueException("The currency must be declared in the package configuration.");
        }

        return $lowercase ? strtolower($normalized) : $normalized;
    }
    
    

    public static function getPaymentMethodsByCountry($country)
    {
        $countryData = (new \Dominservice\DataLocaleParser\DataParser)->parseAllDataPerCountry('en', $country);
        $paymentMethods = ['card'];

        if (!empty($countryData->so)) {
            if (in_array($countryData->so, ['AU', 'CA', 'NZ', 'UK', 'US'])) {
                $paymentMethods[] = 'afterpay_clearpay';
            }
            if (in_array($countryData->so, ['MY', 'SG'])) {
                $paymentMethods[] = 'grabpay';
            }
            if ($countryData->so === 'MX') {
                $paymentMethods[] = 'oxxo';
            }
            if ($countryData->so === 'CA') {
                $paymentMethods[] = 'acss_debit';
            }
            if ($countryData->so === 'AU') {
                $paymentMethods[] = 'au_becs_debit';
            }
            if ($countryData->so === 'MY') {
                $paymentMethods[] = 'fpx';
            }
            if ($countryData->so === 'JP') {
                $paymentMethods[] = 'jcb';
            }

            if ($countryData->continent === 'EU') {
                if($countryData->currency->code === 'EUR') {
                    $paymentMethods[] = 'sepa_debit';
                }
                if ($countryData->so === 'FR') {
                    $paymentMethods[] = 'cartes_bancaires';
                }
                if ($countryData->so === 'UK') {
                    $paymentMethods[] = 'bacs_debit';
                }
                if ($countryData->so === 'BE') {
                    $paymentMethods[] = 'bancontact';
                }
                if ($countryData->so === 'AT') {
                    $paymentMethods[] = 'eps';
                }
                if ($countryData->so === 'DE') {
                    $paymentMethods[] = 'giropay';
                }
                if ($countryData->so === 'NL') {
                    $paymentMethods[] = 'ideal';
                }
                if ($countryData->so === 'PL') {
                    $paymentMethods[] = 'p24';
                }
                if (in_array($countryData->so, ['AT', 'BE', 'DE', 'IT', 'NL', 'ES'])) {
                    $paymentMethods[] = 'sofort';
                }
            }
            if (in_array($countryData->so, ['US', 'CA', 'JP'])) {
                $paymentMethods[] = 'transfers';
            }
        }

        return $paymentMethods;
    }
}