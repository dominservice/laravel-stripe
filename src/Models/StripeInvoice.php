<?php

namespace Dominservice\LaraStripe\Models;

use Dominservice\LaraStripe\Traits\ParentMorph;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stripe\Subscription;

/**
 * @property int $id
 * @property string $subscription_id
 * @property string $stripe_invoice_id
 * @property float $total
 * @property null|\Carbon\Carbon $paid_at
 *
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property null|\Carbon\Carbon $deleted_at
 * @property null|StripeSubscription $subscription
 *
 */
class StripeInvoice extends Model
{
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $fillable = [
        'subscription_id',
        'stripe_invoice_id',
        'currency',
        'total',
        'paid_at',
    ];

    /**
     * @return string
     */
    public function getMorphClass(): string
    {
        return 'stripe_invoice';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function subscription(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(StripeSubscription::class, 'id', 'subscription_id');
    }
}