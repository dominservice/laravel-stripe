<?php

namespace Dominservice\LaraStripe\Repositories;

use Dominservice\LaraStripe\Models\StripeCustomer as StripeCustomerModel;
use Illuminate\Support\Str;


class Repositories
{
    protected array $allowedParameters = [];

    protected array $params = [];

    protected array $extendObjects = [];

    protected array $extendObjectsParam = [];

    protected array $expand = [];

    protected $model;

    protected $object;

    protected function getParams($params = []): array
    {
        if (!empty($this->expand)) {
            $params['expand'] = array_unique($this->expand);
        }

        return array_merge($this->params, $params);
    }

    protected function getOpts(): array
    {
        $opts = [];

        if (isset($this->stripeAccount) && is_string($this->stripeAccount)) {
            $opts[] = $this->stripeAccount;
        }

        return $opts;
    }

    /**
     * @param string|array $expand
     * @return $this
     */
    public function setExpand(string|array $expand): static
    {
        if (is_array($expand)) {
            $this->expand = array_merge($this->expand, $expand);
        } else {
            $this->expand[] = $expand;
        }

        return $this;
    }

    /**
     * @param string|array $param
     * @param $value
     * @return $this
     */
    public function setParam(string|array $param, $value = null): static
    {
        if (is_array($param)) {
            $this->params = array_merge($this->params, $param);
        } elseif (is_array($value) && !empty($this->params[$param])) {
            $this->params[$param] = array_merge($this->params[$param], $value);
        } else {
            $this->params[$param] = $value;
        }

        return $this;
    }

    /**
     * @param string|array $param
     * @param $value
     * @return $this
     */
    public function setExtendObjectsParam(string $extendObject, string|array $param, $value = null): static
    {
        if (empty($this->extendObjectsParam[$extendObject])) {
            $this->extendObjectsParam[$extendObject] = [];
        }

        if (is_array($param)) {
            $this->extendObjectsParam[$extendObject] = array_merge($this->extendObjectsParam[$extendObject], $param);
        } elseif (is_array($value) && !empty($this->extendObjectsParam[$extendObject][$param])) {
            $this->extendObjectsParam[$extendObject][$param] = array_merge($this->extendObjectsParam[$extendObject][$param], $value);
        } else {
            $this->extendObjectsParam[$extendObject][$param] = $value;
        }

        return $this;
    }

    protected function clearObjectParams()
    {
        $this->params = [];
        $this->expand = [];
        $this->extendObjectsParam = [];
    }

    public function __call($m, $a = null)
    {
        $param = Str::snake(preg_replace('#(^get|^set)#', '', $m));

        if (preg_match('#^get#', $m)) {
            return isset($this->$param) ? $this->$param : null;
        } elseif (preg_match('#^set#', $m)) {

            $extendObject = preg_match('#^(extend_)\w+(_).*#', $param)
                ? preg_replace('#^(extend_\w+)(_.*)#', '$1', $param)
                : false;
            $extendObjectParam = preg_match('#^(extend_)\w+(_).*#', $param)
                ? preg_replace('#^(extend_\w+)(_.*)#', '$2', $param)
                : false;

            if (!in_array($param, $this->allowedParameters) && !isset($this->extendObjects[$extendObject])) {
                throw new \RuntimeException("The {$m}() method does not exist inside this repository");
            } else {
                $value = $a[0];

                if (method_exists($this, 'validateParam')) {
                    if ($extendObject) {
                        $this->validateParam($extendObject, $extendObjectParam, $value);
                    } else {
                        $this->validateParam($param, $value);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param $user
     * @param $emptyModel
     * @return bool|StripeCustomerModel
     */
    protected function getCustomerModel($user, $emptyModel = false)
    {
        if (!$item = StripeCustomerModel::where('user_id', $user->{$user->getKeyName()})->first() && $emptyModel) {
            $item = new StripeCustomerModel();
            $item->user_id = $user->{$user->getKeyName()};
            $item->save();
        }

        return $item;
    }

    /**
     * @param $user
     * @return void
     */
    protected function getCustomerIdByUser(&$user)
    {
        if (is_a($user, get_class(new (config('stripe.model'))))) {
            if ($userForStripe = $this->getCustomerModel($user)) {
                $user = $userForStripe->customer_id;
            } else {
                $user = '_empty_';
            }
        }
    }

    public function getModel()
    {
        return $this->model;
    }

    public function getStripeObject()
    {
        return $this->object;
    }
}