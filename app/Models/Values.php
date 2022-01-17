<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Values extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'value_name', 'value_price','attribute_id'
    ];
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}
