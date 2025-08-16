<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Transaction;
use App\Support\PaymentHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Settle payment for multiple transactions
     */
    public function settlePayment(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $transactions = Transaction::lockForUpdate()
                ->whereIn('id', $data['transaction_ids'])
                ->get();

            if ($transactions->isEmpty()) {
                throw new \Exception('No transactions selected');
            }

            // Calculate remaining amounts
            $allocations = [];
            $totalAmount = 0.00;

            foreach ($transactions as $transaction) {
                $remaining = round($transaction->grand_total - $transaction->paid_total, 2);
                if ($remaining <= 0) continue; // Already paid

                $allocations[] = [
                    'transaction' => $transaction,
                    'remaining' => $remaining
                ];
                $totalAmount += $remaining;
            }

            if ($totalAmount <= 0) {
                throw new \Exception('All selected transactions already settled');
            }

            // Validate cash vs non-cash
            $isCash = PaymentHelper::isCash($data['payment_method_id']);
            $tenderedAmount = $data['tendered_amount'] ?? null;
            $changeAmount = 0.00;

            if ($isCash) {
                if ($tenderedAmount === null || $tenderedAmount < $totalAmount) {
                    throw new \Exception('Cash received is insufficient');
                }
                $changeAmount = round($tenderedAmount - $totalAmount, 2);
            } else {
                if (!is_null($tenderedAmount) && (float)$tenderedAmount !== (float)$totalAmount) {
                    throw new \Exception('Non-cash must be exact amount');
                }
            }

            // Create payment
            $payment = Payment::create([
                'payment_method_id' => $data['payment_method_id'],
                'amount' => $totalAmount,
                'tendered_amount' => $isCash ? $tenderedAmount : null,
                'change_amount' => $isCash ? $changeAmount : 0,
                'note' => $data['note'] ?? null,
                'cashier_id' => Auth::id(),
                'received_at' => now(),
            ]);

            // Allocate payments and close transactions
            foreach ($allocations as $allocation) {
                $transaction = $allocation['transaction'];
                $remaining = $allocation['remaining'];

                PaymentTransaction::create([
                    'payment_id' => $payment->id,
                    'transaction_id' => $transaction->id,
                    'allocated_amount' => $remaining,
                ]);

                $transaction->update([
                    'paid_total' => round($transaction->paid_total + $remaining, 2),
                    'balance_due' => 0,
                    'status' => 'completed',
                    'paid_at' => now(),
                ]);
            }

            return $payment;
        });
    }

    /**
     * Delete payment and restore transaction status
     */
    public function deletePayment(Payment $payment): bool
    {
        return DB::transaction(function () use ($payment) {
            // Restore transaction status
            foreach ($payment->transactions as $transaction) {
                $allocatedAmount = $transaction->pivot->allocated_amount;

                $transaction->update([
                    'paid_total' => max(0, $transaction->paid_total - $allocatedAmount),
                    'balance_due' => $transaction->balance_due + $allocatedAmount,
                    'status' => 'pending',
                    'paid_at' => null,
                ]);
            }

            // Delete payment transactions and payment
            $payment->paymentTransactions()->delete();
            return $payment->delete();
        });
    }
}
