<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('webhook_receipts', function (Blueprint $t) {
            $t->id();
            $t->string('provider', 32);
            $t->string('event_hash', 64)->unique();
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('webhook_receipts');
    }
};
