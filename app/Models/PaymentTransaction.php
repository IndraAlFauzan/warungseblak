<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = ['payment_id', 'transaction_id', 'allocated_amount'];

    protected $casts = [
        'allocated_amount' => 'decimal:2'
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
