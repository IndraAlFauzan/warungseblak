<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'transaction_id' => $this->id, // untuk backward compatibility
            'order_no' => $this->order_no,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'created_at_formatted' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at' => $this->when($this->updated_at, $this->updated_at?->format('Y-m-d H:i:s')),
            'table_id' => $this->when($this->table_id, $this->table_id),
            'table_no' => optional($this->table)->table_no,
            'no_table' => optional($this->table)->table_no, // untuk backward compatibility
            'customer_name' => $this->customer_name,
            'user_id' => $this->user_id,
            'service_type' => $this->service_type,
            'status' => $this->status,
            'grand_total' => $this->grand_total,
            'paid_total' => $this->paid_total,
            'balance_due' => $this->balance_due,
            'paid_at' => $this->when($this->paid_at, $this->paid_at?->format('Y-m-d H:i:s')),
            'paid_at_formatted' => $this->when($this->paid_at, $this->paid_at?->format('d/m/Y H:i')),

            // Untuk payment response, include allocated_amount jika ada
            'allocated_amount' => $this->when(
                isset($this->pivot) && $this->pivot instanceof \Illuminate\Database\Eloquent\Relations\Pivot,
                optional($this->pivot)->allocated_amount
            ),

            // Detail transaction
            'details' => TransactionDetailResource::collection($this->whenLoaded('details')),
            'detail_transaction' => TransactionDetailResource::collection($this->whenLoaded('details')), // untuk backward compatibility
        ];
    }
}
