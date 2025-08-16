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
            $payment = $this->paymentService->settlePayment($request->validated());

            // Load relationships for response
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
                'data' => new PaymentResource($payment)
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to settle payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
