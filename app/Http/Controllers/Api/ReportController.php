<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function daily(Request $request)
    {
        $date = $request->query('date', now()->toDateString());

        $report = Transaction::whereDate('created_at', $date)
            ->selectRaw('COUNT(*) as total_transactions, SUM(total) as total_income')
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Daily report retrieved successfully',
            'data' => [
                'date' => $date,
                'total_transactions' => $report->total_transactions ?? 0,
                'total_income' => $report->total_income ?? 0,
            ],
        ], 200);
    }

    public function monthly(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));

        $report = Transaction::whereYear('created_at', substr($month, 0, 4))
            ->whereMonth('created_at', substr($month, 5, 2))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total_transactions, SUM(total) as total_income')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Calculate the total income for the month
        $totalMonthlyIncome = (string) $report->sum('total_income') ?? 0;

        return response()->json([
            'success' => true,
            'message' => 'Monthly report retrieved successfully',
            'total_income' => $totalMonthlyIncome,
            'data' => $report,
        ], 200);
    }

    public function topProducts(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->endOfMonth()->toDateString());

        $report = TransactionDetail::whereBetween('transaction_details.created_at', [$startDate, $endDate])
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->selectRaw('products.name, SUM(transaction_details.quantity) as total_sold')
            ->groupBy('products.name')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Top products retrieved successfully',
            'data' => $report,
        ], 200);
    }

    public function byPayment(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->endOfMonth()->toDateString());

        $report = Transaction::whereBetween('transactions.created_at', [$startDate, $endDate])
            ->join('payment_methods', 'transactions.payment_method_id', '=', 'payment_methods.id')
            ->selectRaw('payment_methods.name as payment_method, COUNT(*) as total_transactions, SUM(transactions.total) as total_income')
            ->groupBy('payment_methods.name')
            ->orderBy('total_income', 'desc')
            ->limit(1)
            ->get();


        return response()->json([
            'success' => true,
            'message' => 'Report by payment method retrieved successfully',
            'data' => $report,
        ], 200);
    }
}
