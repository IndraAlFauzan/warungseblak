<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {

        $methods = PaymentMethod::where('active', 1)->get(['id', 'name', 'type', 'provider', 'channel', 'code']);
        if ($methods->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No payment methods found'], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Payment methods retrieved successfully',
            'data' => [
                'offline' => $methods->where('type', 'offline')->values(),
                'gateway' => $methods->where('type', 'gateway')->values(),
            ]
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:payment_methods',
            'type' => 'required|in:offline,gateway',
            'provider' => 'nullable|string',
            'channel' => 'required|string',
            'code' => 'required|string|unique:payment_methods',
            'active' => 'boolean'
        ]);

        $method = PaymentMethod::create($validated);
        return response()->json([
            'success' => true,
            'message' => 'Payment method created successfully',
            'data' => $method
        ], 201);
    }

    public function show($id)
    {
        $method = PaymentMethod::find($id);
        if (!$method) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method not found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Payment method retrieved successfully',
            'data' => $method
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $method = PaymentMethod::find($id);
        if (!$method) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method not found'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|unique:payment_methods,name,' . $id,
            'type' => 'required|in:offline,gateway',
            'provider' => 'nullable|string',
            'channel' => 'required|string',
            'code' => 'required|string|unique:payment_methods,code,' . $id,
            'active' => 'boolean'
        ]);

        $method->update($validated);
        return response()->json([
            'success' => true,
            'message' => 'Payment method updated successfully',
            'data' => $method->fresh()
        ], 200);
    }

    public function destroy($id)
    {
        $method = PaymentMethod::find($id);
        if (!$method) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method not found'
            ], 404);
        }

        // Check if payment method is used in any payments
        if ($method->payments()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete payment method that has been used in payments'
            ], 422);
        }

        $method->delete();
        return response()->json([
            'success' => true,
            'message' => 'Payment method deleted successfully'
        ], 200);
    }
}
