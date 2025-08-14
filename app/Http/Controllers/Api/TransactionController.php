<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        // Ambil semua transaksi dengan relasi yang diperlukan urutkan dari data terbaru
        // Ambil semua transaksi dengan relasi yang diperlukan
        $transactions = Transaction::with(['user', 'paymentMethod', 'details.product', 'details.flavor', 'details.spicyLevel'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No transactions found',
                'data' => [],
            ], 404);
        }

        // Format ulang data untuk respons
        $formattedTransactions = $transactions->map(function ($transaction) {
            return [
                'transaction_id' => $transaction->id,
                'created_at' => $transaction->created_at,
                'user_id' => $transaction->user_id,
                'payment_method_id' => $transaction->payment_method_id,
                'total' => $transaction->total,
                'payment_amount' => $transaction->payment_amount,
                'change_amount' => $transaction->change_amount,
                'name_user' => $transaction->user->name,
                'payment_method' => $transaction->paymentMethod->name,
                'service_type' => $transaction->service_type,
                'details' => $transaction->details->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'product_id' => $detail->product_id,
                        'name_product' => $detail->product->name,
                        'quantity' => $detail->quantity,
                        'flavor_id' => $detail->flavor_id ?? null,
                        'flavor' => $detail->flavor->name ?? null,
                        'spicy_level_id' => $detail->spicy_level_id ?? null,
                        'spicy_level' => $detail->spicyLevel->name ?? null,
                        'note' => $detail->note,
                        'price' => $detail->price,
                        'subtotal' => $detail->subtotal,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Transactions retrieved successfully',
            'data' => $formattedTransactions,
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payment_amount' => 'required|numeric|min:0',
            'service_type' => 'required|in:dine_in,take_away',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.quantity' => 'required|integer|min:1',
            'details.*.note' => 'nullable|string|max:255',
            'details.*.flavor_id' => 'nullable|exists:flavors,id',
            'details.*.spicy_level_id' => 'nullable|exists:spicy_levels,id',
        ]);

        try {
            // Hitung total transaksi
            $total = 0;

            foreach ($validated['details'] as $detail) {
                $product = Product::findOrFail($detail['product_id']);

                if ($product->stock < $detail['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stock for product '{$product->name}' is insufficient. Available stock: {$product->stock}.",
                    ], 400);
                }

                $subtotal = $product->price * $detail['quantity'];
                $total += $subtotal;

                $product->decrement('stock', $detail['quantity']);
            }

            // Validasi pembayaran
            if ($validated['payment_amount'] < $total) {
                return response()->json([
                    'success' => false,
                    'message' => 'Uang Bayar tidak cukup.',
                ], 400);
            }

            $change = $validated['payment_amount'] - $total;

            // Simpan transaksi
            $transaction = Transaction::create([
                'user_id' => $validated['user_id'],
                'payment_method_id' => $validated['payment_method_id'],
                'total' => $total,
                'payment_amount' => $validated['payment_amount'],
                'change_amount' => $change,
                'service_type' => $validated['service_type'],
            ]);

            // Simpan detail transaksi
            foreach ($validated['details'] as $detail) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'price' => Product::findOrFail($detail['product_id'])->price,
                    'subtotal' => Product::findOrFail($detail['product_id'])->price * $detail['quantity'],
                    'note' => $detail['note'] ?? null,
                    'flavor_id' => $detail['flavor_id'] ?? null,
                    'spicy_level_id' => $detail['spicy_level_id'] ?? null,
                ]);
            }

            //formated response
            $formattedResponse = [
                'transaction_id' => $transaction->id,
                'created_at' => $transaction->created_at,
                'user_id' => $transaction->user_id,
                'payment_method_id' => $transaction->payment_method_id,
                'total' => $transaction->total,
                'payment_amount' => $transaction->payment_amount,
                'change_amount' => $transaction->change_amount,
                'name_user' => $transaction->user->name,
                'payment_method' => $transaction->paymentMethod->name,
                'service_type' => $transaction->service_type,
                'details' => $transaction->details->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'product_id' => $detail->product_id,
                        'name_product' => $detail->product->name,
                        'quantity' => $detail->quantity,
                        'flavor_id' => $detail->flavor_id ?? null,
                        'flavor' => $detail->flavor->name ?? null,
                        'spicy_level_id' => $detail->spicy_level_id ?? null,
                        'spicy_level' => $detail->spicyLevel->name ?? null,
                        'note' => $detail->note,
                        'price' => $detail->price,
                        'subtotal' => $detail->subtotal,
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Transaction created successfully',
                'data' => $formattedResponse,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create transaction',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function show($id)
    {
        $transaction = Transaction::with(['user', 'paymentMethod', 'details.product', 'details.flavor', 'details.spicyLevel'])->find($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
                'data' => [],
            ], 404);
        }

        //formated response
        $formattedResponse = [
            'transaction_id' => $transaction->id,
            'created_at' => $transaction->created_at,
            'user_id' => $transaction->user_id,
            'payment_method_id' => $transaction->payment_method_id,
            'total' => $transaction->total,
            'payment_amount' => $transaction->payment_amount,
            'change_amount' => $transaction->change_amount,
            'name_user' => $transaction->user->name,
            'payment_method' => $transaction->paymentMethod->name,
            'service_type' => $transaction->service_type,
            'details' => $transaction->details->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'product_id' => $detail->product_id,
                    'name_product' => $detail->product->name,
                    'quantity' => $detail->quantity,
                    'flavor_id' => $detail->flavor_id ?? null,
                    'flavor' => $detail->flavor->name ?? null,
                    'spicy_level_id' => $detail->spicy_level_id ?? null,
                    'spicy_level' => $detail->spicyLevel->name ?? null,
                    'note' => $detail->note,
                    'price' => $detail->price,
                    'subtotal' => $detail->subtotal,
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Transaction retrieved successfully',
            'data' => $formattedResponse,
        ], 200);
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

            //formated response (ambil data sebelum dihapus)
            $formattedResponse = [
                'transaction_id' => $transaction->id,
                'created_at' => $transaction->created_at,
                'user_id' => $transaction->user_id,
                'payment_method_id' => $transaction->payment_method_id,
                'total' => $transaction->total,
                'payment_amount' => $transaction->payment_amount,
                'change_amount' => $transaction->change_amount,
                'name_user' => $transaction->user->name,
                'payment_method' => $transaction->paymentMethod->name,
                'service_type' => $transaction->service_type,
                'details' => $transaction->details->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'product_id' => $detail->product_id,
                        'name_product' => $detail->product->name,
                        'quantity' => $detail->quantity,
                        'flavor_id' => $detail->flavor_id ?? null,
                        'flavor' => $detail->flavor->name ?? null,
                        'spicy_level_id' => $detail->spicy_level_id ?? null,
                        'spicy_level' => $detail->spicyLevel->name ?? null,
                        'note' => $detail->note,
                        'price' => $detail->price,
                        'subtotal' => $detail->subtotal,
                    ];
                }),
            ];

            // Hapus detail transaksi
            $transaction->details()->delete();

            // Hapus transaksi
            $transaction->delete();

            return response()->json([
                'success' => true,
                'message' => 'Transaction with ID ' . $id . ' deleted successfully',
                'data' => $formattedResponse,
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
