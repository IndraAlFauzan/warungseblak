<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Str;
use App\Models\Payment;

class XenditGatewayClient
{
    public function __construct(private Client $http) {}

    // Create Invoice (Payment Link)
    public function createInvoice(Payment $p): array
    {
        $externalId = 'INV-' . Str::orderedUuid();

        $res = $this->http->post(config('services.xendit.base') . '/v2/invoices', [
            'auth' => [config('services.xendit.secret'), ''],              // Basic Auth
            'headers' => ['X-IDEMPOTENCY-KEY' => (string) Str::uuid()],
            'json' => [
                'external_id' => $externalId,
                'amount'      => (int) $p->amount, // IDR integer
                'currency'    => 'IDR',
                'description' => 'POS Payment',
            ],
            'timeout' => 10,
        ]);

        $data = json_decode($res->getBody(), true);
        $ref  = $data['id'] ?? $externalId;
        $url  = $data['invoice_url'] ?? $data['payment_link']['url'] ?? null;
        $exp  = isset($data['expiry_date']) ? \Carbon\Carbon::parse($data['expiry_date']) : now()->addMinutes(30);

        return [$ref, $url, $exp, 0.0]; // fee estimasi=0, isi aktual dari webhook jika tersedia
    }
}
