<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'payment_method_id', 'total'];

    // Satu transaksi dibuat oleh satu kasir (BelongsTo ke users).
    // Satu transaksi memiliki banyak detail transaksi (HasMany).
    // Satu transaksi menggunakan satu metode pembayaran (BelongsTo ke payment_methods).

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }
}
