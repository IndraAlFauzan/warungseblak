<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WebhookController extends Controller
{
    public function xendit(Request $r, PaymentService $svc)
    {
        // 0) verifikasi token
        abort_unless($r->header('X-CALLBACK-TOKEN') === config('services.xendit.callback_token'), 401);

        // 1) drop duplikat webhook (opsional kuat)
        if (Schema::hasTable('webhook_receipts')) {
            $hash = hash('sha256', $r->getContent());
            $dup  = DB::table('webhook_receipts')->where('event_hash', $hash)->exists();
            if ($dup) return response()->json(['ok' => true], 200);
            DB::table('webhook_receipts')->insert(['provider' => 'xendit', 'event_hash' => $hash, 'created_at' => now(), 'updated_at' => now()]);
        }

        // 2) ambil referensi & status dari payload invoices
        $providerRef = (string) ($r->input('id') ?? data_get($r->all(), 'data.id'));
        $raw         = strtoupper($r->input('status') ?? data_get($r->all(), 'data.status', ''));
        $status = match ($raw) {
            'PAID', 'SUCCEEDED', 'CAPTURED' => 'PAID',
            'EXPIRED' => 'EXPIRED',
            'FAILED', 'VOIDED', 'CANCELED' => 'FAILED',
            default => 'PENDING',
        };

        // 3) fee opsional
        $fee = (float) ($r->input('fees', 0) ?? data_get($r->all(), 'data.fees', 0));

        // 4) finalize
        $svc->settleFromWebhook($providerRef, $status, ['fee' => $fee, 'raw' => $r->all()]);

        return response()->json(['ok' => true], 200); // penting: balas 2xx
    }
}
