<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\TransactionService;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Get all transactions.
     */
    public function index()
    {
        $transactions = Transaction::with(['user', 'table', 'details.product', 'details.flavor', 'details.spicyLevel'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Transactions retrieved',
            'data' => TransactionResource::collection($transactions)
        ], 200);
    }

    /**
     * Get transactions with 'pending' status.
     */
    public function indexByStatus()
    {
        $transactions = Transaction::with(['user', 'table', 'details.product', 'details.flavor', 'details.spicyLevel'])
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Transactions retrieved',
            'data' => TransactionResource::collection($transactions)
        ], 200);
    }

    /**
     * Create new transaction.
     */
    public function store(StoreTransactionRequest $request)
    {
        try {
            $transaction = $this->transactionService->createTransaction($request->validated());

            $transaction->load(['table', 'details.product', 'details.flavor', 'details.spicyLevel']);

            return response()->json([
                'success' => true,
                'message' => 'Transaction created',
                'data' => new TransactionResource($transaction)
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific transaction.
     */
    public function show($id)
    {
        $transaction = Transaction::with(['user', 'table', 'details.product', 'details.flavor', 'details.spicyLevel'])
            ->find($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
                'data' => []
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Transaction retrieved',
            'data' => new TransactionResource($transaction)
        ], 200);
    }

    /**
     * Update existing transaction by adding new items.
     */
    public function update(UpdateTransactionRequest $request, $id)
    {
        try {
            $transaction = Transaction::with(['details'])->find($id);

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found',
                    'data' => []
                ], 404);
            }

            $updatedTransaction = $this->transactionService->addItemsToTransaction(
                $transaction,
                $request->validated()['details']
            );

            $updatedTransaction->load(['table', 'details.product', 'details.flavor', 'details.spicyLevel']);

            return response()->json([
                'success' => true,
                'message' => 'Transaction updated successfully',
                'data' => new TransactionResource($updatedTransaction)
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete transaction.
     */
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
            $this->transactionService->deleteTransaction($transaction);

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
