<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_method_id')->constrained('payment_methods');
            $table->decimal('amount', 12, 2);
            $table->decimal('tendered_amount', 12, 2)->nullable(); // untuk cash
            $table->decimal('change_amount', 12, 2)->default(0);
            $table->timestamp('received_at')->useCurrent();
            $table->string('note')->nullable();
            $table->foreignId('cashier_id')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->decimal('allocated_amount', 12, 2);
            $table->timestamps();
            $table->unique(['payment_id', 'transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('payments');
    }
};
