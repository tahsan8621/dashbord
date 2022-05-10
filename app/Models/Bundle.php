<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    protected $fillable = [
        'name',
        'discount',
        'image',
        'starting_time',
        'end_time',
        'seller_id'
    ];
}
