<?php

namespace Dominservice\LaraStripe\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $stripe_customer_id
 * @property string|int $user_id
 *
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property null|\Carbon\Carbon $deleted_at
 *
 */
class StripeCustomer extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'stripe_customer_id',
        'user_id',
    ];

    public function getMorphClass()
    {
        return 'stripe_customer';
    }

    public function user()
    {
        $userModel = new (config('stripe.model', \App\Models\User::class));
        
        return $this->hasOne($userModel, $userModel->getKeyName(), 'user_id');
    }
}
