<?php

namespace Modules\Payment\Models;

use Illuminate\Database\Eloquent\Model;

class StripeTransaction extends Model
{
    protected $table = 'stripe_transactions';

    protected $fillable = [
        'user_id',
        'user_type',
        'stripe_payment_intent_id',
        'stripe_charge_id',
        'payment_method_id',
        'amount',
        'currency',
        'status',
        'description',
        'raw_payload',
    ];

    protected $casts = [
        'raw_payload' => 'array',
    ];
}
