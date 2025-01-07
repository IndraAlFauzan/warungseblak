<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    //Satu metode pembayaran digunakan di banyak transaksi (One-to-Many).

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
