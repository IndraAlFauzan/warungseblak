<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'name', 'description', 'price', 'stock', 'photo'];

// Satu produk milik satu kategori (BelongsTo).
// Produk dapat muncul di banyak transaksi melalui detail transaksi (HasManyThrough tidak langsung diperlukan).

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }
}
