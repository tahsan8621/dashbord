<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $guarded=[];
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
