<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('payment_methods')->updateOrInsert(
            ['name' => 'Cash'],
            ['type' => 'offline', 'provider' => null, 'channel' => 'cash', 'code' => 'CASH', 'active' => 1]
        );

        DB::table('payment_methods')->whereIn('name', ['QRIS', 'Transfer'])->update(['active' => 0]);

        DB::table('payment_methods')->upsert([
            ['id' => 10, 'name' => 'Xendit - QRIS',  'type' => 'gateway', 'provider' => 'xendit', 'channel' => 'qris',   'code' => 'XENDIT_QRIS', 'active' => 1],
            ['id' => 11, 'name' => 'Xendit - VA BCA', 'type' => 'gateway', 'provider' => 'xendit', 'channel' => 'va_bca', 'code' => 'XENDIT_VA_BCA', 'active' => 1],
            ['id' => 12, 'name' => 'Xendit - OVO',   'type' => 'gateway', 'provider' => 'xendit', 'channel' => 'ovo',    'code' => 'XENDIT_EWALLET_OVO', 'active' => 1],
        ], ['id'], ['name', 'type', 'provider', 'channel', 'code', 'active']);
    }
}
