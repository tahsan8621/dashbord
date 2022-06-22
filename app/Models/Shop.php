<?php

namespace App\Models;

use http\Env\Request;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
   protected $fillable=[
       'name','image','seller_id','shop_details','shop_header_banner',
       'shop_main_banner',
       'shop_fb_link',
       'shop_tw_link'
   ];

    public function banner()
    {
        return $this->belongsTo(Banner::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }


}
