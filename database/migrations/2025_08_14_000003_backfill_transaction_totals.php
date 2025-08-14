<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE transactions t
            JOIN (
                SELECT transaction_id, SUM(quantity * price) AS total
                FROM transaction_details
                GROUP BY transaction_id
            ) d ON d.transaction_id = t.id
            SET t.grand_total = IFNULL(d.total, 0),
                t.paid_total = 0,
                t.balance_due = IFNULL(d.total, 0)
        ");
    }

    public function down(): void
    {
        // No need to rollback data changes
    }
};
