<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FiltersController extends Controller
{
    public function filterByDiscount($cat)
    {
        $catId = Category::where('name', $cat)->value('id');
        $products = Product::where('category_id', $catId)
            ->whereHas('flashes')
            ->with('flashes')
            ->with('price')
            ->paginate(20);
        //dd($products->total());

        return response()->json($products, 200);
    }

    public function fixedOffer()
    {
        $fixedOfferProducts = Product::where('product_type', 1)
            ->with(['price','flashes'])
            ->paginate(20);
        foreach ($fixedOfferProducts as $product) {
            $product->price->makeHidden('reserve_price');
            $product->reviews = Http::get(env('REVIEWS') . "api/product/reviews/{$product->id}")->json();
        }
        return response()->json($fixedOfferProducts, 200);
    }

    public function negotiable()
    {
        $fixedOfferProducts = Product::where('product_type', 2)
            ->with('price')
            ->paginate(20);
        foreach ($fixedOfferProducts as $product) {
            $product->price->makeHidden('reserve_price');
            $product->reviews = Http::get(env('REVIEWS') . "api/product/reviews/{$product->id}")->json();
        }
        return response()->json($fixedOfferProducts, 200);
    }

    public function auction()
    {
        $fixedOfferProducts = Product::where('product_type', 3)
            ->with('price')
            ->paginate(20);
        foreach ($fixedOfferProducts as $product) {
            $product->price->makeHidden('reserve_price');
            $product->reviews = Http::get(env('REVIEWS') . "api/product/reviews/{$product->id}")->json();
        }
        return response()->json($fixedOfferProducts, 200);
    }
}
