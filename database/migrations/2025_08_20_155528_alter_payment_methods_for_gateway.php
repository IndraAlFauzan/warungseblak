<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $t) {
            $t->enum('type', ['offline', 'gateway'])->default('offline')->after('name');
            $t->string('provider', 32)->nullable()->after('type');   // xendit
            $t->string('channel', 32)->nullable()->after('provider'); // qris, va_bca, ovo, ...
            $t->string('code', 64)->nullable()->after('channel');     // CASH, XENDIT_QRIS, ...
            $t->boolean('active')->default(true)->after('code');
            $t->decimal('fee_percent', 5, 2)->nullable()->after('active');
            $t->decimal('fee_fixed', 12, 2)->nullable()->after('fee_percent');
            $t->json('config')->nullable()->after('fee_fixed');
        });
        DB::statement('ALTER TABLE payment_methods ADD UNIQUE KEY uniq_provider_channel (provider, channel)');
        DB::statement('ALTER TABLE payment_methods ADD UNIQUE KEY uniq_code (code)');
    }
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $t) {
            $t->dropUnique('uniq_provider_channel');
            $t->dropUnique('uniq_code');
            $t->dropColumn(['type', 'provider', 'channel', 'code', 'active', 'fee_percent', 'fee_fixed', 'config']);
        });
    }
};
