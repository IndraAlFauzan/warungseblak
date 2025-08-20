<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) STATUS
        if (!Schema::hasColumn('payments', 'status')) {
            Schema::table('payments', function (Blueprint $t) {
                $t->enum('status', ['pending', 'paid', 'failed', 'expired', 'refunded'])
                    ->default('pending')->after('amount');
            });
        } else {
            // (opsional) pastikan tipe/status sesuai harapan — tanpa Doctrine DBAL pakai raw SQL
            // Hanya jalankan kalau DB kamu MySQL/MariaDB dan kamu memang mau konversi ke ENUM
            try {
                DB::statement("ALTER TABLE payments 
                    MODIFY COLUMN status ENUM('pending','paid','failed','expired','refunded')
                    NOT NULL DEFAULT 'pending'");
            } catch (\Throwable $e) {
                // abaikan jika gagal/tipe sudah cocok (varchar dsb)
            }
        }

        // 2) PROVIDER REF & KOLOM LAIN
        Schema::table('payments', function (Blueprint $t) {
            if (!Schema::hasColumn('payments', 'provider_ref')) {
                $t->string('provider_ref', 128)->nullable()->after('status');
            }
            if (!Schema::hasColumn('payments', 'expires_at')) {
                $t->dateTime('expires_at')->nullable()->after('provider_ref');
            }
            if (!Schema::hasColumn('payments', 'fee_amount')) {
                $t->decimal('fee_amount', 12, 2)->nullable()->after('change_amount');
            }
            if (!Schema::hasColumn('payments', 'net_amount')) {
                $t->decimal('net_amount', 12, 2)->nullable()->after('fee_amount');
            }
            if (!Schema::hasColumn('payments', 'metadata')) {
                $t->json('metadata')->nullable()->after('note');
            }
        });

        // 3) UNIQUE INDEX provider_ref (jika belum ada)
        $idx = DB::selectOne("
            SELECT COUNT(1) AS c
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'payments'
              AND INDEX_NAME = 'uniq_provider_ref'
        ");
        if (($idx->c ?? 0) == 0) {
            try {
                DB::statement('ALTER TABLE payments ADD UNIQUE KEY uniq_provider_ref (provider_ref)');
            } catch (\Throwable $e) {
                // abaikan kalau gagal karena sudah ada index/constraint lain
            }
        }

        // 4) BACKFILL (opsional): kalau kolom status ada tapi NULL, isi default 'paid' untuk histori lama
        try {
            DB::table('payments')->whereNull('status')->update(['status' => 'paid']);
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        // Down migration "aman": hanya drop yang pasti kita tambahkan
        Schema::table('payments', function (Blueprint $t) {
            if (Schema::hasColumn('payments', 'provider_ref')) $t->dropColumn('provider_ref');
            if (Schema::hasColumn('payments', 'expires_at'))   $t->dropColumn('expires_at');
            if (Schema::hasColumn('payments', 'fee_amount'))   $t->dropColumn('fee_amount');
            if (Schema::hasColumn('payments', 'net_amount'))   $t->dropColumn('net_amount');
            if (Schema::hasColumn('payments', 'metadata'))     $t->dropColumn('metadata');
            // Catatan: kolom 'status' sengaja tidak di-drop pada down untuk menghindari kehilangan data lama.
        });

        // Drop index jika ada
        try {
            DB::statement('ALTER TABLE payments DROP INDEX uniq_provider_ref');
        } catch (\Throwable $e) {
        }
    }
};
