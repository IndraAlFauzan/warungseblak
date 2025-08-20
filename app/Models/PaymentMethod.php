<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'provider',
        'channel',
        'code',
        'active',
        'fee_percent',
        'fee_fixed',
        'config' // NEW
    ];
}
