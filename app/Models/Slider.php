<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{

    protected $guarded=[];

    public function images()
    {
        return $this->hasMany(Image::class);
    }

}
