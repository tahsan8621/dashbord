<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable=[
        'seller_id',
        'name',
        'discount',
        'starting_time',
        'end_time',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class,'product_promotion');
    }


}
