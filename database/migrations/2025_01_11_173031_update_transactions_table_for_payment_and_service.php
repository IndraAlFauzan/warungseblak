<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTransactionsTableForPaymentAndService extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('payment_amount', 10, 2)->after('total');
            $table->decimal('change_amount', 10, 2)->after('payment_amount');
            $table->enum('service_type', ['dine_in', 'take_away'])->default('take_away')->after('change_amount');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending')->after('service_type');
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['payment_amount', 'change_amount', 'service_type', 'status']);
        });
    }
}

