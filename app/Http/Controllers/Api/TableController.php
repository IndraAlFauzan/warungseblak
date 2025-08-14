<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index()
    {
        $tables = Table::orderBy('table_no')->get();

        return response()->json([
            'success' => true,
            'message' => 'Tables retrieved successfully',
            'data' => $tables,
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'table_no' => 'required|string|unique:tables,table_no',
            'capacity' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        try {
            $table = Table::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Table created successfully',
                'data' => $table,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create table',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $table = Table::with('transactions')->find($id);

        if (!$table) {
            return response()->json([
                'success' => false,
                'message' => 'Table not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Table retrieved successfully',
            'data' => $table,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $table = Table::find($id);

        if (!$table) {
            return response()->json([
                'success' => false,
                'message' => 'Table not found',
            ], 404);
        }

        $validated = $request->validate([
            'table_no' => 'string|unique:tables,table_no,' . $id,
            'capacity' => 'integer|min:1',
            'is_active' => 'boolean',
        ]);

        try {
            $table->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Table updated successfully',
                'data' => $table,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update table',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $table = Table::find($id);

        if (!$table) {
            return response()->json([
                'success' => false,
                'message' => 'Table not found',
            ], 404);
        }

        // Check if table has transactions
        if ($table->transactions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete table that has transactions',
            ], 400);
        }

        try {
            $table->delete();

            return response()->json([
                'success' => true,
                'message' => 'Table deleted successfully',
                'data' => $table,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete table',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
