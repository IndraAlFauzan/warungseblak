<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'name', 'description', 'price', 'stock', 'photo'];

    protected $casts = ['price' => 'double', 'stock' => 'integer'];

    // Satu produk milik satu kategori (BelongsTo).
    // Produk dapat muncul di banyak transaksi melalui detail transaksi (HasManyThrough tidak langsung diperlukan).
    // Menyembunyikan kolom "photo" dari JSON respons
    protected $hidden = ['photo'];

    protected $appends = ['photo_url'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    // Accessor untuk URL Foto
    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo); // URL lengkap
        }
        return null; // Jika foto tidak ada
    }
}
