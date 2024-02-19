<?php

namespace Dominservice\LaraStripe\Models;

use Dominservice\LaraStripe\Traits\ParentMorph;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $product_id
 * @property string $stripe_price_id
 * @property string $parent_type
 * @property int $parent_id
 * @property string ulid_parent_type
 * @property string $ulid_parent_id
 * @property string $uuid_parent_type
 * @property string $uuid_parent_id
 * @property int|bool $status
 * @property float $price
 * @property string $currency
 * @property int|bool $is_default
 *
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property null|\Carbon\Carbon $deleted_at
 *
 */
class StripePrice extends Model
{
    use SoftDeletes, ParentMorph;
    protected $fillable = [
        'product_id',
        'stripe_price_id',
        'parent_type',
        'parent_id',
        'ulid_parent_type',
        'ulid_parent_id',
        'uuid_parent_type',
        'uuid_parent_id',
        'status',
        'price',
        'currency',
        'is_default',
    ];

    public function getMorphClass()
    {
        return 'stripe_product';
    }

    public function parent()
    {
        return $this->morphTo();
    }

    public function parentUlid()
    {
        return $this->morphTo('parent_ulid');
    }

    public function parentUuid()
    {
        return $this->morphTo('parent_uuid');
    }
}