<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $t) {
            $t->unique(['payment_id', 'transaction_id'], 'uniq_payment_trx');
            $t->index('transaction_id'); // bantu query sisa tagihan
        });
    }
    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $t) {
            $t->dropUnique('uniq_payment_trx');
            $t->dropIndex(['transaction_id']);
        });
    }
};
