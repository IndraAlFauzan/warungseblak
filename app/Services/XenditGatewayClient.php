<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Str;
use App\Models\Payment;

class XenditGatewayClient
{
    public function __construct(private Client $http) {}

    /**
     * @return array [provider_ref, checkout_url, expires_at, est_fee, external_id]
     */
    public function createInvoice(Payment $p, array $opts = []): array
    {
        $externalId = 'INV-' . Str::orderedUuid();

        $payload = [
            'external_id' => $externalId,
            'amount'      => (int) $p->amount,
            'currency'    => 'IDR',
            'description' => $opts['description'] ?? 'POS Payment',
        ];

        $res = $this->http->post(config('services.xendit.base') . '/v2/invoices', [
            'auth'    => [config('services.xendit.secret'), ''],
            'headers' => ['X-IDEMPOTENCY-KEY' => (string) Str::uuid()],
            'json'    => $payload,
            'timeout' => 10,
        ]);

        $data = json_decode($res->getBody(), true);
        $ref  = $data['id'] ?? $externalId;
        $url  = $data['invoice_url'] ?? null;
        $exp  = isset($data['expiry_date']) ? \Carbon\Carbon::parse($data['expiry_date']) : now()->addMinutes(30);

        return [$ref, $url, $exp, 0.0, $externalId];
    }
}
