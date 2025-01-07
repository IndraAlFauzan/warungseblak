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
        $transactions = Transaction::with(['user', 'paymentMethod', 'details.product'])->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No transactions found',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Transactions retrieved successfully',
            'data' => $transactions,
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
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

                // Periksa stok
                if ($product->stock < $detail['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stock for product '{$product->name}' is insufficient. Available stock: {$product->stock}.",
                    ], 400);
                }

                // Kurangi stok produk
                $product->decrement('stock', $detail['quantity']);

                // Hitung subtotal untuk detail transaksi
                $subtotal = $product->price * $detail['quantity'];
                $total += $subtotal;
            }

            // Simpan transaksi
            $transaction = Transaction::create([
                'user_id' => $validated['user_id'],
                'payment_method_id' => $validated['payment_method_id'],
                'total' => $total,
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

            return response()->json([
                'success' => true,
                'message' => 'Transaction created successfully',
                'data' => $transaction->load(['user', 'paymentMethod', 'details.product', 'details.flavor', 'details.spicyLevel']),
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
        $transaction = Transaction::with(['user', 'paymentMethod', 'details.product'])->find($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Transaction retrieved successfully',
            'data' => $transaction,
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

            // Hapus detail transaksi
            $transaction->details()->delete();

            // Hapus transaksi
            $transaction->delete();

            return response()->json([
                'success' => true,
                'message' => 'Transaction deleted successfully',
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
