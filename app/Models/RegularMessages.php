<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegularMessages extends Model
{
    protected $fillable = [
        'product_id',
        'seller_id',
        'msg',
        'user_email',
        'sender_type',
        'status',
        'offer_amount',
        'offer_ending_date'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

}
