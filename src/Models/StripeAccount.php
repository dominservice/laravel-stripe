<?php

namespace Dominservice\LaraStripe\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $stripe_account_id
 * @property string|int $user_id
 * @property bool|int $has_person
 * @property bool|int $has_bank_account
 * @property bool|int $has_payment_card
 * @property bool|int $has_agreement_acceptance
 * @property bool|int $type_account
 *
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property null|\Carbon\Carbon $deleted_at
 *
 */
class StripeAccount extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'stripe_account_id',
        'user_id',
        'has_person',
        'has_bank_account',
        'has_payment_card',
        'has_agreement_acceptance',
        'type_account',
    ];

    protected $casts = [
        'has_agreement_acceptance' => 'bool',
        'has_person' => 'bool',
        'has_bank_account' => 'bool',
        'has_payment_card' => 'bool',
    ];

    public function getMorphClass()
    {
        return 'stripe_account';
    }

    public function user()
    {
        $userModel = config('stripe.model', \App\Models\User::class);

        return $this->hasOne($userModel, $userModel->getKeyName(), 'user_id');
    }

    public function hasFullData()
    {
        return $this->has_agreement_acceptance
            && $this->has_person
            && ($this->has_bank_account || $this->has_payment_card);
    }

    public function accountSetAllComplete()
    {
        $this->has_person = 1;
        $this->has_bank_account = 1;
        $this->has_agreement_acceptance = 1;
        $this->save();
    }
}