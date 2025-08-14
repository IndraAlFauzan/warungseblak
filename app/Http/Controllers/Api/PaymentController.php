<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['method', 'cashier', 'transactions'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Payments retrieved successfully',
            'data' => $payments,
        ], 200);
    }

    public function show($id)
    {
        $payment = Payment::with(['method', 'cashier', 'transactions'])->find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment retrieved successfully',
            'data' => $payment,
        ], 200);
    }

    public function destroy($id)
    {
        $payment = Payment::with('transactions')->find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Reverse payment effects on transactions
            foreach ($payment->transactions as $transaction) {
                $allocatedAmount = $transaction->pivot->allocated_amount;

                $transaction->paid_total -= $allocatedAmount;
                $transaction->balance_due = $transaction->grand_total - $transaction->paid_total;

                if ($transaction->balance_due > 0) {
                    $transaction->status = 'placed'; // or whatever default status
                    $transaction->paid_at = null;
                }

                $transaction->save();
            }

            // Delete payment (cascade will handle payment_transactions)
            $payment->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment deleted successfully',
                'data' => $payment,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
