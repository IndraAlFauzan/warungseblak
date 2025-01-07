<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function products()
    {
        //Satu kategori memiliki banyak produk (One-to-Many).

        return $this->hasMany(Product::class);
    }

    
}
