<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;



class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function index()
    {
        $allProducts = Product::with('reviews', 'brands')
            ->latest()
            ->paginate(20);
        return response()->json($allProducts, 200);
    }

    public function show($id)
    {
        $productWithReviews = Product::with('reviews')
            ->where('id', '=', $id)->get();
        return response()->json($productWithReviews, 200);
    }

    public function store(Request $request)
    {
        // dd($request->file('images'));
        $product = new Product();
        $photo_path = $request->file('images');
        $m_path = time() . $photo_path->getClientOriginalName();

        $photo_path->move('images/products', $m_path);
        $product->images ="https://hrazy.com/public/images/products/" . $m_path;

        $product->name = $request->name;
        $product->sku = $request->sku;
        $product->product_type = $request->product_type;
        $product->total_products = $request->total_products;
        $product->description = $request->description;
        $product->total_sales = $request->total_sales;
        $product->starting_price = $request->starting_price;
        $product->buy_now_price = $request->buy_now_price;
        $product->reserve_price = $request->reserve_price;
        $product->save();
        return response()->json("success", 201);

//        $product= new Product;
//        $product->name=$request->input('name');
//        $product->description=$request->input('description');
//        if($product->save()){
//            return response()->json('success',200);
//        }
//        return response()->json('failed',404);
    }

    public function searchProducts($name)
    {
        //dd(Product::where('name', 'LIKE', "%{$name}%"));
        return Product::where('name', 'LIKE', "%{$name}%")->get();
    }
}
