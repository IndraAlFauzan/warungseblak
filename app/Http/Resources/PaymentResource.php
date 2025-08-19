<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_method_id' => $this->payment_method_id,
            'amount' => $this->amount,
            'tendered_amount' => $this->tendered_amount,
            'change_amount' => $this->change_amount,
            'received_at' => $this->received_at?->format('Y-m-d H:i:s'),
            'note' => $this->note,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            // Simplified payment method name
            'method' => $this->when($this->relationLoaded('method'), function () {
                return optional($this->method)->name;
            }),

            // Simplified cashier name
            'cashier' => $this->when($this->relationLoaded('cashier'), function () {
                return optional($this->cashier)->name;
            }),

            // Related transactions with detailed structure
            'transactions' => $this->when($this->relationLoaded('transactions'), function () {
                return $this->transactions->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'order_no' => $transaction->order_no,
                        'customer_name' => $transaction->customer_name,
                        'no_table' => $transaction->table->table_no,
                        'service_type' => $transaction->service_type,
                        'status' => $transaction->status,
                        'allocated_amount' => $transaction->pivot->allocated_amount ?? '0',
                        'grand_total' => $transaction->grand_total,
                        'paid_total' => $transaction->paid_total,
                        'balance_due' => $transaction->balance_due,
                        'paid_at' => $transaction->paid_at?->format('Y-m-d H:i:s'),
                        'detail_transaction' => $transaction->details ? $transaction->details->map(function ($detail) {
                            return [
                                'id' => $detail->id,
                                'product_id' => $detail->product_id,
                                'product_name' => $detail->product->name ?? '',
                                'quantity' => $detail->quantity,
                                'price' => $detail->price,
                                'subtotal' => $detail->subtotal,
                                'flavor' => $detail->flavor->name ?? null,
                                'spicy_level' => $detail->spicyLevel->name ?? null,
                                'note' => $detail->note,
                            ];
                        }) : [],
                    ];
                });
            }, []),
        ];
    }
}
