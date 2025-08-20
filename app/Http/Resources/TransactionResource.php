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
            'transaction_id' => $this->id, // untuk backward compatibility
            'order_no' => $this->order_no,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->when($this->updated_at, $this->updated_at?->format('Y-m-d H:i:s')),
            // 'table_id' => $this->when($this->table_id, $this->table_id),
            'table_no' => optional($this->table)->table_no,
            // 'no_table' => optional($this->table)->table_no, // untuk backward compatibility
            'customer_name' => $this->customer_name,
            'user_id' => $this->user_id,
            'service_type' => $this->service_type,
            'status' => $this->status,
            'grand_total' => $this->grand_total,
            'paid_total' => $this->paid_total,
            'balance_due' => $this->balance_due,
            'paid_at' => $this->paid_at?->format('Y-m-d H:i:s'),
            // Detail transaction
            'details' => TransactionDetailResource::collection($this->whenLoaded('details')),
        ];
    }
}
