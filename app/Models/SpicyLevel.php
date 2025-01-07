<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpicyLevel extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    //Tabel spicy_levels digunakan secara opsional di detail transaksi

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }
}
