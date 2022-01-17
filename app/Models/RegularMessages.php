<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegularMessages extends Model
{
    protected $fillable = ['product_id',
        'msg',
        'sender_email',
        'to_email',
        'status',
        'offer_amount',
        'offer_ending_date'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

}
