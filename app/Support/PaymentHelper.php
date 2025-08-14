<?php

namespace App\Support;

use App\Models\PaymentMethod;

class PaymentHelper
{
    public static function isCash(int $paymentMethodId): bool
    {
        $method = PaymentMethod::find($paymentMethodId);

        if (!$method) {
            return false;
        }

        // Assume cash if name contains 'cash' or 'tunai' (case insensitive)
        return stripos($method->name, 'cash') !== false ||
            stripos($method->name, 'tunai') !== false;
    }
}
