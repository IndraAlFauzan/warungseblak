<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update enum status untuk transaction dengan nilai yang lebih sesuai
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('pending', 'preparing', 'ready', 'completed', 'cancelled') DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Kembalikan ke enum yang lama
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending'");
    }
};
