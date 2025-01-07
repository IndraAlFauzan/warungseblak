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
        Schema::create('reports', function (Blueprint $table) {
            $table->id(); // ID Laporan
            $table->enum('type', ['daily', 'weekly', 'monthly']); // Jenis laporan
            $table->date('report_date'); // Tanggal laporan
            $table->integer('total_transactions'); // Total transaksi
            $table->decimal('total_income', 10, 2); // Total pendapatan
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
