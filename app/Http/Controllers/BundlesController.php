<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\Product;
use App\Traits\GetUserIdTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BundlesController extends Controller
{
    use GetUserIdTrait;

    public function index(Request $request)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);
        if ($user_id->status() == 400) {
            return response()->json('unauthorized', 200);
        }
        $bundles = Bundle::where('seller_id', $user_id)->paginate(20);
        return response()->json($bundles, 200);
    }

    public function show(Request $request, $id)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);
        if ($user_id->status() == 400) {
            return response()->json('unauthorized', 200);
        }
        $bundle = Bundle::findOrFail($id);
        return response()->json($bundle, 200);
    }

    public function store(Request $request)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);
        if ($user_id->status() == 400) {
            return response()->json('unauthorized', 200);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:bundles|max:255',
            'discount' => 'required|max:255',
            'image' => 'required|max:255',
            'starting_time' => 'required',
            'end_time' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }


        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);

        $bundle = new Bundle();
        if ($request->file('image')) {
            $photo_path = $request->file('image');
            $m_path = time() . $photo_path->getClientOriginalName();
            $photo_path->move('images/bundles', $m_path);
            $bundle->image = env('APP_URL') . "/public/images/bundles/" . $m_path;
        }
        $bundle->name=$request->name;
        $bundle->discount=$request->discount;
        $bundle->starting_time=$request->starting_time;
        $bundle->end_time=$request->end_time;
        $bundle->seller_id= $user_id->json();
        $bundle->save();
        return response()->json('successfully save bundle offer.', 200);
    }

    public function update(Request $request,$id)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);
        if ($user_id->status() == 400) {
            return response()->json('unauthorized', 200);
        }
        $bundle=Bundle::findOrFail($id);
        if($user_id==$bundle->seller_id){
            $bundle->update($request->all());
            return response()->json('successfully updated bundle', 200);
        }

        return response()->json('unauthorized', 200);
    }

    public function destroy(Request $request, $id)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);
        if ($user_id->status() == 400) {
            return response()->json('unauthorized', 200);
        }

        $bundle = Bundle::findOrFail($id);
        if ($user_id == $bundle->seller_id) {
            $bundle->delete();
            return response()->json('successfully deleted bundle', 200);
        }
        return response()->json('unauthorized', 200);

    }

    public function addProduct(Request $request)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);
        if ($user_id->status() == 400) {
            return response()->json('unauthorized', 200);
        }
        $product_ids=$request->products_id;
        foreach ($product_ids as $item){
            $product = Product::findOrFail($item);
            $bundle= Bundle::findOrFail($request->bundle_id);
            $product->bundles()->save($bundle);
        }

        return response()->json('successfully added');
    }
    public function getProductHasPromotion(Request $request,$id)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);
        if ($user_id->status() == 400) {
            return response()->json('unauthorized', 200);
        }

        return Product::whereHas('bundles', function ($query) use ($id) {
            $query->where('bundle_id', $id);
        })->where('user_id', $user_id)->paginate(20);
    }

    public function getProductForPromotion(Request $request)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);
        if ($user_id->status() == 400) {
            return response()->json('unauthorized', 200);
        }
        return Product::doesntHave('bundles')
            ->where('user_id',$user_id)->with('price')->paginate(20);
    }

    public function detachProduct(Request $request,$product_id, $bundle_id)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);
        if ($user_id->status() == 400) {
            return response()->json('unauthorized', 200);
        }
        $product = Product::whereHas('bundles', function ($query) use ($product_id, $bundle_id) {
            $query->where('bundle_id', $bundle_id)->where('product_id', $product_id);
        })->first();

        if ($product) {
            $product->bundles()->detach($bundle_id);
            return response()->json('successfully remove product from flash');
        }
        return response()->json('This product do not attached with this flash' );

    }
}
