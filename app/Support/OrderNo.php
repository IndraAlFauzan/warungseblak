<?php

namespace App\Support;

use App\Models\Transaction;

class OrderNo
{
    public static function generate(?int $tableId = null): string
    {
        $prefix = 'ORD';
        $date = date('Ymd');

        if ($tableId) {
            $prefix = "T{$tableId}";
        }

        // Count today's transactions
        $count = Transaction::whereDate('created_at', today())->count();
        $sequence = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        return "{$prefix}-{$date}-{$sequence}";
    }
}
