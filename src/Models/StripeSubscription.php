<?php

namespace Dominservice\LaraStripe\Models;

use Dominservice\LaraStripe\Traits\ParentMorph;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $customer_id
 * @property string $stripe_subscription_id
 * @property string $stripe_checkout_session_id
 * @property string $description
 * @property bool|int $cancel_at_period_end
 * @property bool|int $is_canceled
 *
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property null|\Carbon\Carbon $deleted_at
 *
 */
class StripeSubscription extends Model
{
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $fillable = [
        'customer_id',
        'stripe_subscription_id',
        'stripe_checkout_session_id',
        'description',
        'cancel_at_period_end',
        'is_canceled',
    ];

    /**
     * @return string
     */
    public function getMorphClass(): string
    {
        return 'stripe_product';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function prices(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            StripePrice::class,
            'stripe_subscription_prices',
            'subscription_id',
            'price_id',
            'id',
            'id',
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StripeInvoice::class, 'subcription_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function customer(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(StripeCustomer::class, 'id', 'customer_id');
    }

    /**
     * @param $stripePriceId
     * @return void
     */
    public function syncPrice($stripePriceId): void
    {
        $query = StripePrice::query();

        if (is_array($stripePriceId)) {
            $query->whereIn('stripe_price_id', $stripePriceId);
        } else {
            $query->where('stripe_price_id', $stripePriceId);
        }

        $items = $query->get();

        if ($items->count()) {
            $this->prices()->sync($items->pluck('id')->toArray());
        }
    }
}
