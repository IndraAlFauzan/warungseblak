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
        $payments = Payment::with(['method', 'cashier', 'transactions.table', 'transactions.details.product', 'transactions.details.flavor', 'transactions.details.spicyLevel'])
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedPayments = $payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'payment_method_id' => $payment->payment_method_id,
                'amount' => $payment->amount,
                'tendered_amount' => $payment->tendered_amount,
                'change_amount' => $payment->change_amount,
                'received_at' => $payment->received_at,
                'note' => $payment->note,
                'cashier_id' => $payment->cashier_id,
                'created_at' => $payment->created_at,
                'updated_at' => $payment->updated_at,
                'method' => optional($payment->method)->name,
                'cashier' => optional($payment->cashier)->name,
                'transactions' => $payment->transactions->map(function ($transaction) use ($payment) {
                    return [
                        'id' => $transaction->id,
                        'order_no' => $transaction->order_no,
                        'customer_name' => $transaction->customer_name,
                        'no_table' => optional($transaction->table)->table_no,
                        'service_type' => $transaction->service_type,
                        'status' => $transaction->status,
                        'allocated_amount' => $transaction->pivot->allocated_amount,
                        'grand_total' => $transaction->grand_total,
                        'paid_total' => $transaction->paid_total,
                        'balance_due' => $transaction->balance_due,
                        'paid_at' => $transaction->paid_at,
                        'detail_transaction' => $transaction->details->map(function ($detail) {
                            return [
                                'id' => $detail->id,
                                'product_id' => $detail->product_id,
                                'product_name' => $detail->product->name,
                                'quantity' => $detail->quantity,
                                'price' => $detail->price,
                                'subtotal' => $detail->subtotal,
                                'flavor' => optional($detail->flavor)->name,
                                'spicy_level' => optional($detail->spicyLevel)->name,
                                'note' => $detail->note,
                            ];
                        })
                    ];
                })
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Payments retrieved successfully',
            'data' => $formattedPayments,
        ], 200);
    }

    public function show($id)
    {
        $payment = Payment::with(['method', 'cashier', 'transactions.table', 'transactions.details.product', 'transactions.details.flavor', 'transactions.details.spicyLevel'])->find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        $formattedPayment = [
            'id' => $payment->id,
            'payment_method_id' => $payment->payment_method_id,
            'amount' => $payment->amount,
            'tendered_amount' => $payment->tendered_amount,
            'change_amount' => $payment->change_amount,
            'received_at' => $payment->received_at,
            'note' => $payment->note,
            'cashier_id' => $payment->cashier_id,
            'created_at' => $payment->created_at,
            'updated_at' => $payment->updated_at,
            'method' => optional($payment->method)->name,
            'cashier' => optional($payment->cashier)->name,
            'transactions' => $payment->transactions->map(function ($transaction) use ($payment) {
                return [
                    'id' => $transaction->id,
                    'order_no' => $transaction->order_no,
                    'customer_name' => $transaction->customer_name,
                    'no_table' => optional($transaction->table)->table_no,
                    'service_type' => $transaction->service_type,
                    'status' => $transaction->status,
                    'allocated_amount' => $transaction->pivot->allocated_amount,
                    'grand_total' => $transaction->grand_total,
                    'paid_total' => $transaction->paid_total,
                    'balance_due' => $transaction->balance_due,
                    'paid_at' => $transaction->paid_at,
                    'detail_transaction' => $transaction->details->map(function ($detail) {
                        return [
                            'id' => $detail->id,
                            'product_id' => $detail->product_id,
                            'product_name' => $detail->product->name,
                            'quantity' => $detail->quantity,
                            'price' => $detail->price,
                            'subtotal' => $detail->subtotal,
                            'flavor' => optional($detail->flavor)->name,
                            'spicy_level' => optional($detail->spicyLevel)->name,
                            'note' => $detail->note,
                        ];
                    })
                ];
            })
        ];


        return response()->json([
            'success' => true,
            'message' => 'Payment retrieved successfully',
            'data' => $formattedPayment,
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
