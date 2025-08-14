<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Hapus foreign key constraint terlebih dahulu
            $table->dropForeign(['payment_method_id']);
            // Hapus kolom payment_method_id karena payment sudah dipisah ke tabel payments
            $table->dropColumn('payment_method_id');

            // Hapus kolom payment lama yang tidak diperlukan
            if (Schema::hasColumn('transactions', 'payment_amount')) {
                $table->dropColumn('payment_amount');
            }
            if (Schema::hasColumn('transactions', 'change_amount')) {
                $table->dropColumn('change_amount');
            }
            if (Schema::hasColumn('transactions', 'total')) {
                $table->dropColumn('total');
            }

            // Tambahkan kolom yang diperlukan jika belum ada
            if (!Schema::hasColumn('transactions', 'order_no')) {
                $table->string('order_no')->unique()->after('id');
            }
            if (!Schema::hasColumn('transactions', 'customer_name')) {
                $table->string('customer_name')->after('order_no');
            }
            if (!Schema::hasColumn('transactions', 'table_id')) {
                $table->foreignId('table_id')->nullable()->constrained('tables')->after('customer_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Kembalikan kolom payment_method_id
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->after('user_id');

            // Kembalikan kolom payment lama
            $table->decimal('payment_amount', 10, 2)->nullable()->after('grand_total');
            $table->decimal('change_amount', 10, 2)->nullable()->after('payment_amount');
            $table->decimal('total', 10, 2)->nullable()->after('change_amount');

            // Hapus kolom yang ditambahkan di up()
            if (Schema::hasColumn('transactions', 'order_no')) {
                $table->dropColumn('order_no');
            }
            if (Schema::hasColumn('transactions', 'customer_name')) {
                $table->dropColumn('customer_name');
            }
            if (Schema::hasColumn('transactions', 'table_id')) {
                $table->dropForeign(['table_id']);
                $table->dropColumn('table_id');
            }
        });
    }
};
