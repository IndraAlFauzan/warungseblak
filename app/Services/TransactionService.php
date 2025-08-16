<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Product;
use App\Support\OrderNo;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Create a new transaction with details
     */
    public function createTransaction(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $total = 0;

            // Validate and calculate total
            foreach ($data['details'] as $detail) {
                $product = Product::lockForUpdate()->findOrFail($detail['product_id']);
                if ($product->stock < $detail['quantity']) {
                    throw new \Exception("Stock '{$product->name}' kurang. Tersisa: {$product->stock}");
                }
                $subtotal = $product->price * $detail['quantity'];
                $total += $subtotal;
            }

            // Create transaction
            $transaction = Transaction::create([
                'order_no' => OrderNo::generate($data['table_id'] ?? null),
                'customer_name' => $data['customer_name'] ?? null,
                'table_id' => $data['table_id'] ?? null,
                'user_id' => $data['user_id'],
                'service_type' => $data['service_type'],
                'status' => 'pending',
                'grand_total' => $total,
                'paid_total' => 0,
                'balance_due' => $total,
            ]);

            // Create details and update stock
            foreach ($data['details'] as $detail) {
                $product = Product::lockForUpdate()->findOrFail($detail['product_id']);
                $product->decrement('stock', $detail['quantity']);

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'price' => $product->price,
                    'subtotal' => $product->price * $detail['quantity'],
                    'note' => $detail['note'] ?? null,
                    'flavor_id' => $detail['flavor_id'] ?? null,
                    'spicy_level_id' => $detail['spicy_level_id'] ?? null,
                ]);
            }

            return $transaction;
        });
    }

    /**
     * Add items to existing transaction
     */
    public function addItemsToTransaction(Transaction $transaction, array $details): Transaction
    {
        if ($transaction->status !== 'pending') {
            throw new \Exception('Cannot update transaction. Only pending transactions can be updated.');
        }

        return DB::transaction(function () use ($transaction, $details) {
            $additionalTotal = 0;

            // Validate stock for new items
            foreach ($details as $detail) {
                $product = Product::lockForUpdate()->findOrFail($detail['product_id']);
                if ($product->stock < $detail['quantity']) {
                    throw new \Exception("Stock '{$product->name}' kurang. Tersisa: {$product->stock}");
                }
                $subtotal = $product->price * $detail['quantity'];
                $additionalTotal += $subtotal;
            }

            // Add new details and update stock
            foreach ($details as $detail) {
                $product = Product::lockForUpdate()->findOrFail($detail['product_id']);
                $product->decrement('stock', $detail['quantity']);

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'price' => $product->price,
                    'subtotal' => $product->price * $detail['quantity'],
                    'note' => $detail['note'] ?? null,
                    'flavor_id' => $detail['flavor_id'] ?? null,
                    'spicy_level_id' => $detail['spicy_level_id'] ?? null,
                ]);
            }

            // Update transaction totals
            $newGrandTotal = $transaction->grand_total + $additionalTotal;
            $newBalanceDue = $transaction->balance_due + $additionalTotal;

            $transaction->update([
                'grand_total' => $newGrandTotal,
                'balance_due' => $newBalanceDue,
            ]);

            return $transaction->fresh();
        });
    }

    /**
     * Delete transaction and restore stock
     */
    public function deleteTransaction(Transaction $transaction): bool
    {
        return DB::transaction(function () use ($transaction) {
            // Restore stock
            foreach ($transaction->details as $detail) {
                $product = Product::find($detail->product_id);
                if ($product) {
                    $product->increment('stock', $detail->quantity);
                }
            }

            // Delete details and transaction
            $transaction->details()->delete();
            return $transaction->delete();
        });
    }
}
