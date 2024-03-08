<?php

namespace Dominservice\LaraStripe\Models;

use Dominservice\LaraStripe\Traits\ParentMorph;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $stripe_product_id
 * @property string $parent_type
 * @property int $parent_id
 * @property string ulid_parent_type
 * @property string $ulid_parent_id
 * @property string $uuid_parent_type
 * @property string $uuid_parent_id
 * @property float $price
 *
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property null|\Carbon\Carbon $deleted_at
 * 
 * @property null|StripePrice $defaultPrice
 */
class StripeProduct extends Model
{
    use SoftDeletes, ParentMorph;
    protected $fillable = [
        'stripe_product_id',
        'parent_type',
        'parent_id',
        'ulid_parent_type',
        'ulid_parent_id',
        'uuid_parent_type',
        'uuid_parent_id',
        'price',
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
        return $this->morphTo(__FUNCTION__, 'ulid_parent_type', 'ulid_parent_id');
    }

    public function parentUuid()
    {
        return $this->morphTo(__FUNCTION__, 'uuid_parent_type', 'uuid_parent_id');
    }

    public function defaultPrice()
    {
        return $this->hasOne(StripePrice::class, 'product_id', 'id')
            ->where('is_default', 1);
    }

    public function prices()
    {
        return $this->hasMany(StripePrice::class, 'product_id', 'id');
    }
}
