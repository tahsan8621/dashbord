<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Traits\RequestTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class BrandsController extends Controller
{
    use RequestTrait;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function index(Request $request)
    {
        $user_token = $request->bearerToken();
        $url=env('SELLER_USER_API') . 'user';
        $get_user_id=$this->getUserId($user_token,$url);
        if ($get_user_id->status() == 401) {
            return response()->json('unauthorized user',401);
        }
        $brands = Brand::where('user_id',$get_user_id)->get();
        return response()->json($brands, 200);
    }

    public function show($id)
    {
        $brand= Brand::findOrFail($id);
        return response()->json($brand, 200);
    }

    public function store(Request $request)
    {
        $user_token = $request->bearerToken();
        $url=env('SELLER_USER_API') . 'user';
        $get_user_id=$this->getUserId($user_token,$url);
        if ($get_user_id->status() == 401) {
            return response()->json('unauthorized user',401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:brands|max:255',
            'image' => 'required|mimes:jpg,png|max:460',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(),200);
        }

        $validated = $validator->validated();
        if($validated){
            $brand = new Brand();
            $brand->name = $request->name;
            $brand->user_id = $get_user_id;
            $brand->status = 0;
            $photo_path = $request->file('image');
            $m_path = time() . $photo_path->getClientOriginalName();
            $photo_path->move('images/brands', $m_path);
            $brand->image ="https://hrazy.com/public/images/brands/" . $m_path;

            $brand->save();
            return response()->json("success", 200);
        }


    }


}
