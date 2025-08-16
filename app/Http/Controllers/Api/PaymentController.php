<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Services\PaymentService;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Get all payments with transactions
     */
    public function index()
    {
        $payments = Payment::with([
            'method',
            'cashier',
            'transactions.table',
            'transactions.details.product',
            'transactions.details.flavor',
            'transactions.details.spicyLevel'
        ])->orderByDesc('created_at')->get();

        return response()->json([
            'success' => true,
            'message' => 'Payments retrieved',
            'data' => PaymentResource::collection($payments)
        ], 200);
    }

    /**
     * Get specific payment with transactions
     */
    public function show($id)
    {
        $payment = Payment::with([
            'method',
            'cashier',
            'transactions.table',
            'transactions.details.product',
            'transactions.details.flavor',
            'transactions.details.spicyLevel'
        ])->find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
                'data' => []
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment retrieved',
            'data' => new PaymentResource($payment)
        ], 200);
    }

    /**
     * Delete payment
     */
    public function destroy($id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
                'data' => []
            ], 404);
        }

        try {
            $this->paymentService->deletePayment($payment);

            return response()->json([
                'success' => true,
                'message' => 'Payment deleted successfully',
                'data' => ['payment_id' => $id]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
