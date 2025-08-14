<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.quantity' => 'required|integer|min:1|max:99',
            'details.*.note' => 'nullable|string|max:255',
            'details.*.flavor_id' => 'nullable|exists:flavors,id',
            'details.*.spicy_level_id' => 'nullable|exists:spicy_levels,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'details.required' => 'Detail item wajib diisi',
            'details.array' => 'Detail item harus berupa array',
            'details.min' => 'Minimal harus ada 1 item',

            'details.*.product_id.required' => 'Product ID wajib diisi untuk setiap item',
            'details.*.product_id.exists' => 'Product tidak ditemukan',

            'details.*.quantity.required' => 'Quantity wajib diisi untuk setiap item',
            'details.*.quantity.integer' => 'Quantity harus berupa angka',
            'details.*.quantity.min' => 'Quantity minimal 1',
            'details.*.quantity.max' => 'Quantity maksimal 99',

            'details.*.note.string' => 'Note harus berupa text',
            'details.*.note.max' => 'Note maksimal 255 karakter',

            'details.*.flavor_id.exists' => 'Flavor tidak ditemukan',
            'details.*.spicy_level_id.exists' => 'Spicy level tidak ditemukan',
        ];
    }
}
