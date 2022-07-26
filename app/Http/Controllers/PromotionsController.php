<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Promotion;
use App\Traits\GetUserIdTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PromotionsController extends Controller
{
    use GetUserIdTrait;

    public function index(Request $request)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);
        if ($user_id->status() == 400) {
            return response()->json('unauthorized', 200);
        }

        $seller_promotions = Promotion::where('seller_id', $user_id)->get();
        return response()->json($seller_promotions, 200);
    }

    public function show(Request $request, $id)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);
        if ($user_id->status() == 400) {
            return response()->json('unauthorized', 200);
        }

        $promotion = Promotion::findOrFail($id);
        return response()->json($promotion, 200);
    }

    public function store(Request $request)
    {

        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);
        if ($user_id->status() == 400) {
            return response()->json('unauthorized', 200);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:promotions|max:255',
            'discount' => 'required|max:255',
            'starting_time' => 'required',
            'end_time' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }


        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);

        $promotion = new Promotion();
        $data = $request->all() + array("seller_id" => $user_id->json());
        $promotion->create($data);
        return response()->json('successfully save promotional offer.', 200);
    }

    public function update(Request $request, $id)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);
        if ($user_id->status() == 400) {
            return response()->json('unauthorized', 200);
        }
        $promotion = Promotion::findOrFail($id);
        if ($user_id == $promotion->seller_id) {
            $promotion->update($request->all());
            return response()->json('successfully updated promotion', 200);
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

        $promotion = Promotion::findOrFail($id);
        if ($user_id == $promotion->seller_id) {
            $promotion->delete();
            return response()->json('successfully deleted promotion', 200);
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
        $product_ids = $request->products_id;
        foreach ($product_ids as $item) {
            $product = Product::findOrFail($item);
            $promotion = Promotion::findOrFail($request->promotion_id);
            $product->promotions()->save($promotion);
        }

        return response()->json('successfully added');
    }

    public function getProductHasPromotion(Request $request, $id)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);
        if ($user_id->status() == 400) {
            return response()->json('unauthorized', 200);
        }
        $allProducts = Product::whereHas('promotions', function ($query) use ($id) {
            $query->where('promotion_id', $id);
        })->where('user_id', $user_id)->paginate(20);

        return response()->json($allProducts, 200);
    }

    public function getProductForPromotion(Request $request)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);
        if ($user_id->status() == 400) {
            return response()->json('unauthorized', 200);
        }
        return Product::doesntHave('promotions')->where('user_id', $user_id)->with('price')->paginate(20);
    }

    public function detachProduct(Request $request,$product_id, $promotion_id)
    {

        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);
        if ($user_id->status() == 400) {
            return response()->json('unauthorized', 200);
        }
        $product = Product::whereHas('promotions', function ($query) use ($product_id, $promotion_id) {
            $query->where('promotion_id', $promotion_id)->where('product_id', $product_id);
        })->first();

        if ($product) {
            $product->promotions()->detach($promotion_id);
            return response()->json('successfully remove product from promotion');
        }
        return response()->json('This product do not attached with this promotion');

    }


}
