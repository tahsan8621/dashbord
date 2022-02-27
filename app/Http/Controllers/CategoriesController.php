<?php

namespace App\Http\Controllers;

use App\Models\Category;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Traits\RequestTrait;


class CategoriesController extends Controller
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

    public function index()
    {

        $allCategories = Category::whereNull('parent_id')->with('children')->get()->makeHidden([
            'parent_id',
            'description',
            'request_status',
            'created_at',
            'updated_at'
        ]);

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
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user_token = $request->bearerToken();
        $url = env('SELLER_USER_API') . 'user';
        $get_user_id = $this->getUserId($user_token, $url);


        if ($get_user_id->status() != 401) {
            $category = new Category();
            $category->name = $request->name;

            if ($request->parent_id != "null") {
                $category->parent_id = $request->parent_id;
            }
            $category->description = $request->description;
            $category->request_status = 1;
            $category->user_id = $get_user_id;
            if ($request->user_type == "seller") {
                $category->user_type = 0;
            } else {
                $category->user_type = 1;
            }
            $category->save();
            return response()->json("success", 200);
        }
        return response()->json("unauthorized user", 401);


    }

    public function sellerCategories(Request $request)
    {
        $user_token = $request->bearerToken();
        $url=env('SELLER_USER_API') . 'user';
        $get_user_id=$this->getUserId($user_token,$url);

        if ($get_user_id->status() == 401) {
            return response()->json('unauthorized user',401);
        }
        $categories=Category::where('user_id',$get_user_id)->where('user_type',0)->get();
        return response()->json($categories,200);
    }


}
