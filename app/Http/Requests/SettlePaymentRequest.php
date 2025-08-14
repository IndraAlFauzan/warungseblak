<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettlePaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'payment_method_id' => 'required|exists:payment_methods,id',
            'transaction_ids' => 'required|array|min:1',
            'transaction_ids.*' => 'required|exists:transactions,id',
            'tendered_amount' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:255',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
