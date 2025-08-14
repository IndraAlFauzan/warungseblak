<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_no',
        'customer_name',
        'table_id',
        'user_id',
        'service_type',
        'status',
        'grand_total',
        'paid_total',
        'balance_due',
        'paid_at'
    ];

    protected $casts = [
        'grand_total' => 'decimal:2',
        'paid_total' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'paid_at' => 'datetime'
    ];

    // Satu transaksi dibuat oleh satu kasir (BelongsTo ke users).
    // Satu transaksi memiliki banyak detail transaksi (HasMany).
    // Satu transaksi bisa dibayar dengan banyak payment (ManyToMany melalui payment_transactions).

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function payments()
    {
        return $this->belongsToMany(Payment::class, 'payment_transactions')
            ->withPivot('allocated_amount')
            ->withTimestamps();
    }
}
