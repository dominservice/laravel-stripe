<?php

namespace Dominservice\LaraStripe\Models;

use Dominservice\LaraStripe\Traits\ParentMorph;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $product_id
 * @property string $stripe_price_id
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
        'status',
        'price',
        'currency',
        'is_default',
    ];
}
