<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Support\OrderNo;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index()
    {
        $query = Transaction::with(['user', 'table', 'details.product', 'details.flavor', 'details.spicyLevel']);

        // only get transactions with 'pending' 
        $transactions = $query->where('status', 'pending')
            ->orderByDesc('created_at')->get();

        $data = $transactions->map(function ($t) {
            return [
                'transaction_id' => $t->id,
                'order_no' => $t->order_no,
                'created_at' => $t->created_at,
                'table_no' => optional($t->table)->table_no,
                'customer_name' => $t->customer_name,
                'user_id' => $t->user_id,
                'service_type' => $t->service_type,
                'status' => $t->status,
                'grand_total' => $t->grand_total,
                'paid_total' => $t->paid_total,
                'balance_due' => $t->balance_due,
                'details' => $t->details->map(function ($d) {
                    return [
                        'id' => $d->id,
                        'product_id' => $d->product_id,
                        'name_product' => $d->product->name,
                        'quantity' => $d->quantity,
                        'flavor_id' => $d->flavor_id,
                        'flavor' => optional($d->flavor)->name,
                        'spicy_level_id' => $d->spicy_level_id,
                        'spicy_level' => optional($d->spicyLevel)->name,
                        'note' => $d->note,
                        'price' => $d->price,
                        'subtotal' => $d->subtotal,
                    ];
                }),
            ];
        });

        return response()->json(['success' => true, 'message' => 'Transactions retrieved', 'data' => $data], 200);
    }

    public function store(StoreTransactionRequest $request)
    {
        $v = $request->validated();

        try {
            /** @var Transaction|null $transaction */
            $transaction = null;

            DB::transaction(function () use (&$transaction, $v) {
                $total = 0;

                // hitung & lock stok
                foreach ($v['details'] as $d) {
                    $product = Product::lockForUpdate()->findOrFail($d['product_id']);
                    if ($product->stock < $d['quantity']) {
                        abort(422, "Stock '{$product->name}' kurang. Tersisa: {$product->stock}");
                    }
                    $subtotal = $product->price * $d['quantity'];
                    $total += $subtotal;
                }

                // buat transaksi
                $transaction = Transaction::create([
                    'order_no' => OrderNo::generate($v['table_id'] ?? null),
                    'customer_name' => $v['customer_name'] ?? null,
                    'table_id' => $v['table_id'] ?? null,
                    'user_id' => $v['user_id'],
                    'service_type' => $v['service_type'],
                    'status' => 'pending',
                    'grand_total' => $total,
                    'paid_total' => 0,
                    'balance_due' => $total,
                ]);

                // simpan detail + kurangi stok
                foreach ($v['details'] as $d) {
                    $product = Product::lockForUpdate()->findOrFail($d['product_id']);
                    $product->decrement('stock', $d['quantity']);

                    TransactionDetail::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $d['product_id'],
                        'quantity' => $d['quantity'],
                        'price' => $product->price,
                        'subtotal' => $product->price * $d['quantity'],
                        'note' => $d['note'] ?? null,
                        'flavor_id' => $d['flavor_id'] ?? null,
                        'spicy_level_id' => $d['spicy_level_id'] ?? null,
                    ]);
                }
            });

            // Ensure transaction was created
            if (!$transaction) {
                throw new \Exception('Transaction creation failed');
            }

            // response
            $transaction->load(['table', 'details.product', 'details.flavor', 'details.spicyLevel']);
            $formatted = [
                'transaction_id' => $transaction->id,
                'order_no' => $transaction->order_no,
                'created_at' => $transaction->created_at,
                'table_id' => $transaction->table_id,
                'table_no' => optional($transaction->table)->table_no,
                'customer_name' => $transaction->customer_name,
                'user_id' => $transaction->user_id,
                'service_type' => $transaction->service_type,
                'status' => $transaction->status,
                'grand_total' => $transaction->grand_total,
                'paid_total' => $transaction->paid_total,
                'balance_due' => $transaction->balance_due,
                'details' => $transaction->details->map(function ($d) {
                    return [
                        'id' => $d->id,
                        'product_id' => $d->product_id,
                        'name_product' => $d->product->name,
                        'quantity' => $d->quantity,
                        'flavor_id' => $d->flavor_id,
                        'flavor' => optional($d->flavor)->name,
                        'spicy_level_id' => $d->spicy_level_id,
                        'spicy_level' => optional($d->spicyLevel)->name,
                        'note' => $d->note,
                        'price' => $d->price,
                        'subtotal' => $d->subtotal,
                    ];
                }),
            ];

            return response()->json(['success' => true, 'message' => 'Transaction created', 'data' => $formatted], 201);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Failed to create transaction', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $t = Transaction::with(['user', 'table', 'details.product', 'details.flavor', 'details.spicyLevel'])->find($id);
        if (!$t) return response()->json(['success' => false, 'message' => 'Transaction not found', 'data' => []], 404);

        $data = [
            'transaction_id' => $t->id,
            'order_no' => $t->order_no,
            'created_at' => $t->created_at,
            'table_no' => optional($t->table)->table_no,
            'customer_name' => $t->customer_name,
            'user_id' => $t->user_id,
            'service_type' => $t->service_type,
            'status' => $t->status,
            'grand_total' => $t->grand_total,
            'paid_total' => $t->paid_total,
            'balance_due' => $t->balance_due,
            'details' => $t->details->map(function ($d) {
                return [
                    'id' => $d->id,
                    'product_id' => $d->product_id,
                    'name_product' => $d->product->name,
                    'quantity' => $d->quantity,
                    'flavor_id' => $d->flavor_id,
                    'flavor' => optional($d->flavor)->name,
                    'spicy_level_id' => $d->spicy_level_id,
                    'spicy_level' => optional($d->spicyLevel)->name,
                    'note' => $d->note,
                    'price' => $d->price,
                    'subtotal' => $d->subtotal,
                ];
            }),
        ];

        return response()->json(['success' => true, 'message' => 'Transaction retrieved', 'data' => $data], 200);
    }

    public function destroy($id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
                'data' => [],
            ], 404);
        }

        try {
            // Kembalikan stok produk saat transaksi dihapus
            foreach ($transaction->details as $detail) {
                $product = Product::find($detail->product_id);
                if ($product) {
                    $product->increment('stock', $detail->quantity);
                }
            }

            // Hapus detail transaksi
            $transaction->details()->delete();

            // Hapus transaksi
            $transaction->delete();

            return response()->json([
                'success' => true,
                'message' => 'Transaction deleted successfully',
                'data' => ['transaction_id' => $id],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete transaction',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
