<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete(); // Relasi ke transaksi
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete(); // Produk yang dipesan
            $table->foreignId('flavor_id')->nullable()->constrained('flavors')->nullOnDelete(); // Rasa produk (opsional)
            $table->foreignId('spicy_level_id')->nullable()->constrained('spicy_levels')->nullOnDelete(); // Level pedas produk (opsional)
            $table->integer('quantity'); // Jumlah produk
            $table->decimal('price', 10, 2); // Harga satuan produk
            $table->decimal('subtotal', 10, 2); // quantity * price
            $table->text('note')->nullable(); // Catatan khusus (e.g., "Tanpa bawang")
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};
