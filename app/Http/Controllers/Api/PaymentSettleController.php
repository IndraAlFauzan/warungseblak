<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettlePaymentRequest;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Transaction;
use App\Support\PaymentHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentSettleController extends Controller
{
    public function store(SettlePaymentRequest $request)
    {
        $v = $request->validated();

        return DB::transaction(function () use ($v) {
            $txns = Transaction::lockForUpdate()
                ->whereIn('id', $v['transaction_ids'])
                ->get();

            if ($txns->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No transactions selected'], 422);
            }

            // hitung sisa & total amount
            $allocs = [];
            $amount = 0.00;
            foreach ($txns as $t) {
                $remaining = round($t->grand_total - $t->paid_total, 2);
                if ($remaining <= 0) continue; // sudah lunas
                $allocs[] = ['transaction' => $t, 'remaining' => $remaining];
                $amount += $remaining;
            }
            if ($amount <= 0) {
                return response()->json(['success' => false, 'message' => 'All selected transactions already settled'], 422);
            }

            // validasi tunai vs non-tunai
            $isCash = PaymentHelper::isCash($v['payment_method_id']);
            $tendered = $v['tendered_amount'] ?? null;
            $change = 0.00;

            if ($isCash) {
                if ($tendered === null || $tendered < $amount) {
                    return response()->json(['success' => false, 'message' => 'Cash received is insufficient'], 422);
                }
                $change = round($tendered - $amount, 2);
            } else {
                if (!is_null($tendered) && (float)$tendered !== (float)$amount) {
                    return response()->json(['success' => false, 'message' => 'Non-cash must be exact amount'], 422);
                }
            }

            // buat payment
            $payment = Payment::create([
                'payment_method_id' => $v['payment_method_id'],
                'amount' => $amount,
                'tendered_amount' => $isCash ? $tendered : null,
                'change_amount' => $isCash ? $change : 0,
                'note' => $v['note'] ?? null,
                'cashier_id' => Auth::id(),
                'received_at' => now(),
            ]);

            // alokasikan penuh & tutup tiap transaksi
            foreach ($allocs as $a) {
                /** @var \App\Models\Transaction $t */
                $t = $a['transaction'];
                $remaining = $a['remaining'];

                PaymentTransaction::create([
                    'payment_id' => $payment->id,
                    'transaction_id' => $t->id,
                    'allocated_amount' => $remaining,
                ]);

                $t->paid_total = round($t->paid_total + $remaining, 2);
                $t->balance_due = 0;
                $t->status = 'completed';
                $t->paid_at = now();
                $t->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment settled',
                'data' => [
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                    'tendered_amount' => $payment->tendered_amount,
                    'change_amount' => $payment->change_amount,
                    'settled_transactions' => $txns->pluck('id')->values(),
                ]
            ], 201);
        });
    }
}
