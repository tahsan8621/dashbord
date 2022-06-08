<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BestSellersController extends Controller{
    public function index()
    {
        $getAvgTotalSale = Product::avg("total_sales");
        $bestSellers = Product::where("total_sales", ">", $getAvgTotalSale)
            ->distinct()
            ->with(["flashes","price"])
            ->paginate(1);


        foreach ($bestSellers as $item) {
            $item->price->makeHidden('reserve_price');
            $item->flashes->makeHidden('pivot');
            $item->reviews = Http::get(env('REVIEWS') . "api/product/reviews/{$item->id}")->json();
            //$item->flashes[0]->created_at= $item->flashes[0]->created_at->addDays(1);
        }
        return response()->json($bestSellers);
    }
}
