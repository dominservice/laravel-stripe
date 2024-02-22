<?php

namespace Dominservice\LaraStripe\Repositories;


use Dominservice\LaraStripe\Exception\ParameterBadValueException;
use Dominservice\LaraStripe\Helpers\ValidateHelper;
use Illuminate\Support\Arr;
use Stripe\Exception\ApiErrorException;
use Stripe\Service\BillingPortal\SessionService;

class BillingPortalSession extends Repositories
{
    /**
     * @var array
     */
    protected array $allowedParameters = [
        'customer',
        'configuration',
        'flow_data',
        'locale',
        'on_behalf_of',
        'return_url',
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
     * @return \Stripe\BillingPortal\Session
     * @throws ApiErrorException
     * @throws \Dominservice\LaraStripe\Exception\NoParametersException
     */
    public function create()
    {
        if (empty($this->params['customer'])) {
            throw new \Dominservice\LaraStripe\Exception\NoParametersException("The 'customer' parameter is required.");
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

            if ($param === 'on_behalf_of' && !isset($this->stripeAccount)) {
                throw new ParameterBadValueException("The 'on_behalf_of' parameter is used exclusively for Stripe Connect");
            }

            $this->setParam($param, $val);
        }
    }
}
