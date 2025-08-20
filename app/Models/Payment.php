<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'payment_method_id',
        'amount',
        'tendered_amount',
        'change_amount',
        'note',
        'cashier_id',
        'received_at',
        'status',
        'provider_ref',
        'expires_at',
        'fee_amount',
        'net_amount',
        'metadata', // NEW
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tendered_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'received_at' => 'datetime',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function method()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }
    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function transactions()
    {
        return $this->belongsToMany(Transaction::class, 'payment_transactions')
            ->withPivot('allocated_amount')->withTimestamps();
    }

    // bantu deletePayment()
    public function paymentTransactions()
    {
        return $this->hasMany(\App\Models\PaymentTransaction::class, 'payment_id');
    }
}
