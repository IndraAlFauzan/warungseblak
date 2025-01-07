<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'flavor_id',
        'spicy_level_id',
        'quantity',
        'price',
        'subtotal',
        'note'
    ];

    //     Satu detail transaksi milik satu transaksi (BelongsTo).
    // Satu detail transaksi milik satu produk (BelongsTo).
    // Rasa dan level pedas opsional (BelongsTo ke flavors dan spicy_levels).

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function flavor()
    {
        return $this->belongsTo(Flavor::class);
    }

    public function spicyLevel()
    {
        return $this->belongsTo(SpicyLevel::class);
    }
}
