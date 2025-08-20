<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettlePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Services\PaymentService;

class PaymentSettleController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function store(SettlePaymentRequest $request)
    {
        try {
            $data = $request->validated();
            $isCash = \App\Support\PaymentHelper::isCash($data['payment_method_id']);

            if ($isCash) {
                // CASH → pakai service lama (langsung paid)
                $payment = $this->paymentService->settlePayment($data);
                $payment->load([
                    'method',
                    'cashier',
                    'transactions.table',
                    'transactions.details.product',
                    'transactions.details.flavor',
                    'transactions.details.spicyLevel'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment settled',
                    'data' => new \App\Http\Resources\PaymentResource($payment)
                ], 201);
            }

            // GATEWAY → create pending + invoice url
            [$payment, $checkoutUrl] = $this->paymentService->createGateway(
                $data['payment_method_id'],
                $data['transaction_ids'],
                $data['note'] ?? null
            );
            $payment->load([
                'method',
                'cashier',
                'transactions.table',
                'transactions.details.product',
                'transactions.details.flavor',
                'transactions.details.spicyLevel'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment created (pending). Complete via gateway.',
                'checkout_url' => $checkoutUrl,
                'data' => new \App\Http\Resources\PaymentResource($payment)
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create/settle payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
