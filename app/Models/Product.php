<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use  HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'sku',
        'description',
        'total_products',
        'total_sales',
        'status',
        'user_id',
        'image',
        'image_1',
        'image_2',
        'image_3',
        'image_4',
    ];

    public function reviews()
    {
        return $this->belongsToMany(Review::class);
    }
    public function brand()
    {
        return $this->hasOne(Price::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }
    public function price()
    {
        return $this->hasOne(Price::class);
    }
    public function attributes()
    {
        return $this->hasMany(Attribute::class);
    }
    public function messages()
    {
        return $this->hasMany(RegularMessages::class);
    }

    public function hasMessage()
    {
        return $this->messages()->where('product_id',2);
    }

}
