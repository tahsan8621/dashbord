<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class CategoriesController extends Controller
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
        $allCategories = Category::all();
        return response()->json($allCategories, 200);
    }

    public function show($id)
    {
        $productWithReviews = Category::findOrFail($id);
        return response()->json($productWithReviews, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'order_no' => 'required',
            'description' => 'required',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $product = new Category();
        $product->name = $request->name;
        $product->save();
        return response()->json("success", 200);


    }


}
