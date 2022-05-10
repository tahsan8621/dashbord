<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flash extends Model
{
    protected $fillable=[
        'seller_id',
        'name',
        'discount'
    ];
    public function products()
    {
        return $this->belongsToMany(Product::class,'flash_product');
    }
}
