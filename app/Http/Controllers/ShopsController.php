<?php

namespace App\Http\Controllers;


use App\Http\Requests\StoreShopRequest;
use App\Models\Product;
use App\Models\Shop;
use App\Traits\GetUserIdTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class ShopsController extends Controller
{
    use GetUserIdTrait;

    public function index(Request $request)
    {
        $user_token = $request->bearerToken();
        $url = env('SELLER_USER_API') . 'user';
        $res = $this->getUserId($user_token, $url);
        if (isset($res->original)) {
            return response()->json('unauthorized', 401);
        }
        $shops = Shop::where('seller_id', $res->body())->get();
        return response()->json($shops, 200);
    }

    public function show($id)
    {
        $shop = Shop::where('id', $id)->with('banner')->get();
        $products = Product::where('shop_id', $id)->get();
        return response()->json(['shop' => $shop, 'products' => $products], 200);
    }

    public function store(StoreShopRequest $request)
    {
        $res_data = $request->all();
        $validated = Validator::make($request->all(), [
            'name' => 'unique:shops|max:255',
            'image' => 'mimes:jpeg,jpg,png,gif|max:10000',
            'header_banner' => 'mimes:jpeg,jpg,png,gif|max:10000',
            'main_banner' => 'mimes:jpeg,jpg,png,gif|max:10000',
        ]);
        if ($validated->fails()) {
            return response()->json($validated->errors());
        }
        $user_token = $request->bearerToken();
        $url = env('SELLER_USER_API') . 'user';
        $res = $this->getUserId($user_token, $url);
        if (isset($res->original)) {
            return response()->json('unauthorized', 401);
        }
        $hasShops = Shop::where('seller_id', $res->body())->get();
        $shop = new Shop();
        $res_data['seller_id'] = $res->body();

        if ($hasShops->count() < 1) {
            $res_data['authorized'] = 1;
        }

        if ($request->file('image')) {
            $photo_path = $request->file('image');
            $m_path = time() . $photo_path->getClientOriginalName();
            $photo_path->move('images/shop', $m_path);
            $res_data['image'] = env('APP_URL') . "/public/images/shop/" . $m_path;
        }

        if ($request->file('header_banner')) {
            $photo_path = $request->file('header_banner');
            $m_path = time() . $photo_path->getClientOriginalName();
            $photo_path->move('images/shop/banner', $m_path);
            $res_data['shop_header_banner'] = env('APP_URL') . "/public/images/shop/banner/" . $m_path;
        }

        if ($request->file('main_banner')) {
            $photo_path = $request->file('main_banner');
            $m_path = time() . $photo_path->getClientOriginalName();
            $photo_path->move('images/shop/banner', $m_path);
            $res_data['shop_main_banner'] = env('APP_URL') . "/public/images/shop/banner/" . $m_path;
        }

        $shop->create($res_data);
        return response()->json('successfully shop added');
    }

    public function update(Request $request, $id)
    {
        $res_data = $request->all();

        $validator = Validator::make($request->all(), [
            'name' => 'unique:shops|max:255',
            'image' => 'mimes:jpeg,jpg,png,gif|max:10000',
            'header_banner' => 'mimes:jpeg,jpg,png,gif|max:10000',
            'main_banner' => 'mimes:jpeg,jpg,png,gif|max:10000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $user_token = $request->bearerToken();
        $url = env('SELLER_USER_API') . 'user';
        $res = $this->getUserId($user_token, $url);
        if (isset($res->original)) {
            return response()->json('unauthorized', 401);
        }
        $shop = Shop::find($id);
        if ($shop && $shop->seller_id == $res->body()) {
            if ($request->file('image')) {
                $photo_path = $request->file('image');
                $m_path = time() . $photo_path->getClientOriginalName();
                $photo_path->move('images/shop', $m_path);
                $res_data['image'] = env('APP_URL') . "/public/images/shop/" . $m_path;
                $shop->image = env('APP_URL') . "/public/images/shop/" . $m_path;
            }

            if ($request->file('shop_header_banner')) {
                $photo_path = $request->file('shop_header_banner');
                $m_path = time() . $photo_path->getClientOriginalName();
                $photo_path->move('images/shop/banner', $m_path);
                $shop->shop_header_banner = env('APP_URL') . "/public/images/shop/banner/" . $m_path;
                $res_data['shop_header_banner'] = env('APP_URL') . "/public/images/shop/banner/" . $m_path;
            }

            if ($request->file('shop_main_banner')) {
                $photo_path = $request->file('shop_main_banner');
                $m_path = time() . $photo_path->getClientOriginalName();
                $photo_path->move('images/shop/banner', $m_path);
                $shop->shop_main_banner = env('APP_URL') . "/public/images/shop/banner/" . $m_path;
                $res_data['shop_main_banner'] = env('APP_URL') . "/public/images/shop/banner/" . $m_path;
            }
            $shop->update($res_data);
            return response()->json('successfully shop updated');

        } else {
            return response()->json('unauthorized', 401);
        }
    }

    public function distroy(Request $request, $id)
    {
        $user_token = $request->bearerToken();
        $url = env('SELLER_USER_API') . 'user';
        $res = $this->getUserId($user_token, $url);
        if (isset($res->original)) {
            return response()->json('unauthorized', 401);
        }
        $shop = Shop::find($id);
        if ($shop && $shop->seller_id == $res->body()) {
            $shop->delete();
            return response()->json('successfully deleted');
        } else {
            return response()->json('unauthorized', 401);
        }
    }

    public function getAllShops()
    {
        $shops = Shop::get();
        return response()->json($shops, 200);
    }
}
