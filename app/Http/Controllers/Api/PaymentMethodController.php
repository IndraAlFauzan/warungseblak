<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $methods = PaymentMethod::all();
        if ($methods->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No payment methods found'], 404);
        }
        return response()->json(['success' => true, 
        'message' => 'Payment methods retrieved successfully',
        'data' => $methods],
         200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|unique:payment_methods', 'description' => 'nullable|string']);
        $method = PaymentMethod::create($validated);
        return response()->json(['success' => true, 'message' => 'Payment method created successfully', 'data' => $method], 201);
    }

    public function show($id)
    {
        $method = PaymentMethod::find($id);
        if (!$method) {
            return response()->json(['success' => false, 'message' => 'Payment method not found'], 404);
        }
        return response()->json(['success' => true,
        'message' => 'Payment method retrieved successfully'
        , 'data' => $method], 200);
    }

    public function update(Request $request, $id)
    {
        $method = PaymentMethod::find($id);
        if (!$method) {
            return response()->json(['success' => false, 'message' => 'Payment method not found'], 404);
        }
        $validated = $request->validate(['name' => 'required|string|unique:payment_methods,name,' . $id, 'description' => 'nullable|string']);
        $method->update($validated);
        return response()->json(['success' => true,
        
        'message' => 'Payment method updated successfully', 'data' => $method], 200);
    }

    public function destroy($id)
    {
        $method = PaymentMethod::find($id);
        if (!$method) {
            return response()->json(['success' => false, 'message' => 'Payment method not found'], 404);
        }
        $method->delete();
        return response()->json(['success' => true, 'message' => 'Payment method deleted successfully'], 200);
    }
}
