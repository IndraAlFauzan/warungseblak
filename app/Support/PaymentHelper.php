<?php

namespace App\Support;

use App\Models\PaymentMethod;

class PaymentHelper
{
    public static function isCash(int $paymentMethodId): bool
    {
        $m = PaymentMethod::find($paymentMethodId);
        return $m && $m->type === 'offline' && $m->channel === 'cash';
    }
}
