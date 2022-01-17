<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class BrandsController extends Controller
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

        $brands = Brand::all();
        return response()->json($brands, 200);
    }

    public function show($id)
    {
        $brand= Brand::findOrFail($id);
        return response()->json($brand, 200);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:brands|max:255',
            'status' => 'required|integer',
            'image' => 'required|mimes:jpg,png|max:250',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(),200);
        }

        $validated = $validator->validated();
        if($validated){
            $brand = new Brand();
            $brand->name = $request->name;
            $brand->status = $request->status;
            $photo_path = $request->file('image');
            $m_path = time() . $photo_path->getClientOriginalName();


            $photo_path->move('images/brands', $m_path);
            $brand->image ="https://hrazy.com/public/images/brands/" . $m_path;

            $brand->save();
            return response()->json("success", 200);
        }


    }


}
