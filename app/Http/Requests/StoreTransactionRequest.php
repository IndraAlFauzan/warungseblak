<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'table_id' => 'nullable|exists:tables,id',
            'customer_name' => 'nullable|string|max:100',
            'user_id' => 'required|exists:users,id',
            'service_type' => 'required|in:dine_in,take_away',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.quantity' => 'required|integer|min:1',
            'details.*.note' => 'nullable|string|max:255',
            'details.*.flavor_id' => 'nullable|exists:flavors,id',
            'details.*.spicy_level_id' => 'nullable|exists:spicy_levels,id',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
