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
}
