<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Hanya tambahkan kolom yang belum ada
            if (!Schema::hasColumn('transactions', 'grand_total')) {
                $table->decimal('grand_total', 12, 2)->default(0)->after('status');
            }
            if (!Schema::hasColumn('transactions', 'paid_total')) {
                $table->decimal('paid_total', 12, 2)->default(0)->after('grand_total');
            }
            if (!Schema::hasColumn('transactions', 'balance_due')) {
                $table->decimal('balance_due', 12, 2)->default(0)->after('paid_total');
            }
            if (!Schema::hasColumn('transactions', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('balance_due');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Hanya drop kolom yang kita tambahkan di migration ini
            if (Schema::hasColumn('transactions', 'grand_total')) {
                $table->dropColumn('grand_total');
            }
            if (Schema::hasColumn('transactions', 'paid_total')) {
                $table->dropColumn('paid_total');
            }
            if (Schema::hasColumn('transactions', 'balance_due')) {
                $table->dropColumn('balance_due');
            }
            if (Schema::hasColumn('transactions', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
        });
    }
};
