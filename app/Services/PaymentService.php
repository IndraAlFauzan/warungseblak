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
     * Create gateway payment (pending until webhook confirms)
     */
    public function createGateway(int $paymentMethodId, array $transactionIds, ?string $note = null): array
    {
        return DB::transaction(function () use ($paymentMethodId, $transactionIds, $note) {
            $transactions = Transaction::lockForUpdate()
                ->whereIn('id', $transactionIds)->get();
            if ($transactions->isEmpty()) throw new \Exception('No transactions selected');

            $total = 0.00;
            $allocs = [];

            foreach ($transactions as $t) {
                // kurangi dengan alokasi pending yang sudah "booking" transaksi ini
                $pendingReserved = DB::table('payment_transactions as pt')
                    ->join('payments as p', 'p.id', '=', 'pt.payment_id')
                    ->where('p.status', 'pending')
                    ->where('pt.transaction_id', $t->id)
                    ->sum('pt.allocated_amount');

                $due = round($t->grand_total - $t->paid_total - $pendingReserved, 2);
                if ($due <= 0) continue;

                $allocs[] = ['transaction' => $t, 'due' => $due];
                $total += $due;
            }

            if ($total <= 0) throw new \Exception('All selected transactions already settled or reserved');

            $p = Payment::create([
                'payment_method_id' => $paymentMethodId,
                'amount' => $total,
                'status' => 'pending',
                'note' => $note,
                'cashier_id' => Auth::id(),
            ]);

            foreach ($allocs as $a) {
                \App\Models\PaymentTransaction::create([
                    'payment_id' => $p->id,
                    'transaction_id' => $a['transaction']->id,
                    'allocated_amount' => $a['due'],
                ]);
            }

            [$ref, $url, $exp, $estFee] = app(\App\Services\XenditGatewayClient::class)->createInvoice($p);

            $p->update([
                'provider_ref' => $ref,
                'expires_at' => $exp,
                'fee_amount' => $estFee,
                'net_amount' => $total - ($estFee ?? 0),
            ]);

            return [$p->fresh(['method', 'transactions']), $url];
        });
    }

    /**
     * Settle payment from webhook (idempotent + invariants)
     */
    public function settleFromWebhook(string $providerRef, string $status, array $meta = []): Payment
    {
        return DB::transaction(function () use ($providerRef, $status, $meta) {
            $p = Payment::where('provider_ref', $providerRef)->lockForUpdate()->firstOrFail();

            // idempotent
            if ($p->status === 'paid') return $p;

            // jika akan menjadi paid, pastikan alokasi valid
            if ($status === 'PAID') {
                $allocatedSum = $p->transactions()->sum('payment_transactions.allocated_amount');
                if (bccomp((string)$allocatedSum, (string)$p->amount, 2) !== 0) {
                    throw new \RuntimeException('Allocated sum mismatch with payment amount');
                }
            }

            if ($status === 'PAID') {
                $p->update([
                    'status' => 'paid',
                    'received_at' => now(),
                    'fee_amount' => $meta['fee'] ?? $p->fee_amount,
                    'net_amount' => $p->amount - ($meta['fee'] ?? 0),
                    'metadata' => array_merge($p->metadata ?? [], $meta),
                ]);

                foreach ($p->transactions as $t) {
                    $alloc = (float)$t->pivot->allocated_amount;
                    $due   = max(0, $t->grand_total - $t->paid_total);
                    $apply = min($alloc, $due);
                    if ($apply > 0) {
                        $newPaid = $t->paid_total + $apply;
                        $t->update([
                            'paid_total'  => $newPaid,
                            'balance_due' => max(0, $t->grand_total - $newPaid),
                            'status'      => $newPaid >= $t->grand_total ? 'completed' : $t->status,
                            'paid_at'     => $newPaid >= $t->grand_total ? now() : $t->paid_at,
                        ]);
                    }
                }
            } elseif ($status === 'EXPIRED') {
                $p->update(['status' => 'expired']);
                // opsional: bebaskan alokasi agar bisa dibayar ulang
                // $p->transactions()->detach();
            } else { // FAILED
                $p->update(['status' => 'failed']);
            }

            return $p->fresh(['method', 'transactions']);
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
