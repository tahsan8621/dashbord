<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Price extends Model
{
    use HasFactory;

    protected $table = 'price_product';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'bidding_time',
        'starting_price',
        'buy_now_price',
        'reserve_price'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
