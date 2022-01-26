<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\Price;
use App\Models\Product;
use App\Models\Value;
use GuzzleHttp\Client;
use Illuminate\Http\Request;


class ProductsController extends Controller
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

    public function index(Request $request)
    {
        $user_token = $request->bearerToken();

        $client = new Client();

        $user_id = $client->get(env('USER_API_BASE').'user', [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_token,
                'Accept' => 'application/json',
            ],
        ])->getBody()->getContents();
        if ($user_id == null || $user_id == 0) {
            return response()->json('unauthorized', 200);
        }
        $products = Product::with('price', 'reviews')
            ->where('user_id', '=', $user_id)
            ->latest()
            ->paginate(20);
        return $products;
    }

    public function allProducts()
    {

        $allProducts = Product::with('price', 'reviews')
            ->latest()
            ->paginate(20);


        return response()->json($allProducts, 200);
    }

    public function show($id)
    {

        $product=Product::findOrFail($id);

        $client = new Client();

        $user_infos = $client->get(env('USER_API_BASE')."user-infos/".$product->user_id)->getBody()->getContents();

        $productWithReviews = Product::with('reviews')
            ->where('id', '=', $id)->get();

        $attrs = Attribute::with('values')
            ->where('product_id', '=', $id)
            ->get();
        $prices=Price::where('product_id','=',$id)->get(['bidding_time','starting_price','buy_now_price']);


        if (count($productWithReviews)) {

            return response()->json(['products' => $productWithReviews, 'attributes' => $attrs,'prices'=>$prices,'seller_infos'=>json_decode($user_infos)], 200);
        }
        return response()->json('product doesn\'t found', '401');
    }

    public function showEdit(Request $request, $id)
    {

        $user_token = $request->bearerToken();
        $client = new Client();

        $user_id = $client->get(env('USER_API_BASE').'user', [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_token,
                'Accept' => 'application/json',
            ],
        ])->getBody()->getContents();

        $productWithReviews = Product::with('price', 'reviews')
            ->where('id', '=', $id)->get();

        $attrs = Attribute::with('values')
            ->where('product_id', '=', $id)
            ->get();
        if ($user_id == $productWithReviews->user_id) {
            if (count($productWithReviews)) {
                return response()->json(['products' => $productWithReviews, 'attributes' => $attrs], 200);
            }
        }
        return response()->json('product doesn\'t found', '401');
    }

    public function store(Request $request)
    {
        $user_token = $request->bearerToken();
        $client = new Client();

        $user_id = $client->get(env('USER_API_BASE').'user', [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_token,
                'Accept' => 'application/json',
            ],
        ])->getBody()->getContents();


        $product = new Product();
        if ($request->file('image')) {
            $photo_path = $request->file('image');

            $m_path = time() . $photo_path->getClientOriginalName();


            $photo_path->move('images/products', $m_path);
            $product->image = env('APP_URL')."/public/images/products/" . $m_path;
        }
        if ($request->file('image_1')) {
            $photo_path = $request->file('image_1');
            $m_path = time() . $photo_path->getClientOriginalName();

            $photo_path->move('images/products', $m_path);
            $product->image_1 = env('APP_URL')."/public/images/products/" . $m_path;
        }
        if ($request->file('image_2')) {
            $photo_path = $request->file('image_2');
            $m_path = time() . $photo_path->getClientOriginalName();
            $photo_path->move('images/products', $m_path);
            $product->image_2 = env('APP_URL')."/public/images/products/" . $m_path;
        }
        if ($request->file('image_3')) {
            $photo_path = $request->file('image_3');
            $m_path = time() . $photo_path->getClientOriginalName();
            $photo_path->move('images/products', $m_path);
            $product->image_3 = env('APP_URL')."/public/images/products/" . $m_path;
        }
        if ($request->file('image_4')) {
            $photo_path = $request->file('image_4');
            $m_path = time() . $photo_path->getClientOriginalName();
            $photo_path->move('images/products', $m_path);
            $product->image_4 = env('APP_URL')."/public/images/products/" . $m_path;
        }
        $product->name = $request->name;
        $product->sku = $request->sku;
        $product->product_type = $request->product_type;
        $product->total_products = $request->total_products;
        $product->description = $request->description;
        $product->total_sales = $request->total_sales;
        $product->status = $request->status;
        $product->user_id = $user_id;

        $product->save();

        $bidding_time = $request->bidding_time;
        $starting_price = $request->starting_price;
        $buy_now_price = $request->buy_now_price;
        $reserve_price = $request->reserve_price;
        $converted_date = date('Y-m-d h:i:s', strtotime($bidding_time));


        $product->price()->create([
            'starting_price' => $starting_price,
            'buy_now_price' => $buy_now_price,
            'reserve_price' => $reserve_price,
            'bidding_time' => $converted_date
        ]);


        $att_array = json_decode($request->new_attribute);
        if ($att_array) {
            $count = count($att_array);
            for ($limit = 0; $limit < $count; $limit++) {
                $attr = new Attribute();
                $attr->name = $att_array[$limit]->attribute_name;
                $attr->product_id = $product->id;

                $attr->save();
                $count_values = count($att_array[$limit]->attribute_value);
                for ($i = 0; $i < $count_values; $i++) {
                    $values = new Value();
                    $values->value_name = $att_array[$limit]->attribute_value[$i]->value;
                    $values->attribute_id = $attr->id;
                    $values->value_price = $att_array[$limit]->attribute_value[$i]->price;
                    $values->save();
                }
            }
        }


        return response()->json("successfully save your product", 200);

    }


    public function update($id, Request $request)
    {
        $user_token = $request->bearerToken();

        $client = new Client();

        $user_id = $client->get(env('USER_API_BASE').'user', [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_token,
                'Accept' => 'application/json',
            ],
        ])->getBody()->getContents();
        if ($user_id == null || $user_id == 0) {
            return response()->json('unauthorized', 200);
        }

        $product = Product::findOrFail($id);

        if ($user_id == $product->user_id) {
            $photo_path = $request->file('image');
            $m_path = time() . $photo_path->getClientOriginalName();


            $photo_path->move('images/products', $m_path);
            $product->image = env('APP_URL')."/public/images/products/" . $m_path;

            if ($request->file('image_1')) {
                $photo_path = $request->file('image_1');
                $m_path = time() . $photo_path->getClientOriginalName();

                $photo_path->move('images/products', $m_path);
                $product->image_1 = env('APP_URL')."/public/images/products/" . $m_path;
            }
            if ($request->file('image_2')) {
                $photo_path = $request->file('image_2');
                $m_path = time() . $photo_path->getClientOriginalName();
                $photo_path->move('images/products', $m_path);
                $product->image_2 = env('APP_URL')."/public/images/products/" . $m_path;
            }
            if ($request->file('image_3')) {
                $photo_path = $request->file('image_3');
                $m_path = time() . $photo_path->getClientOriginalName();
                $photo_path->move('images/products', $m_path);
                $product->image_3 = env('APP_URL')."/public/images/products/" . $m_path;
            }
            if ($request->file('image_4')) {
                $photo_path = $request->file('image_4');
                $m_path = time() . $photo_path->getClientOriginalName();
                $photo_path->move('images/products', $m_path);
                $product->image_4 = env('APP_URL')."/public/images/products/" . $m_path;
            }
            $product->name = $request->name;
            $product->sku = $request->sku;
            $product->product_type = $request->product_type;
            $product->total_products = $request->total_products;
            $product->description = $request->description;
            $product->total_sales = $request->total_sales;
            $product->save();
            return response()->json("successfully update your product", 200);
        }
        return response()->json('unauthorized', 200);
    }

    public function destroy(Request $request, $id)
    {
        $user_token = $request->bearerToken();

        $client = new Client();

        $user_id = $client->get(env('USER_API_BASE').'user', [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_token,
                'Accept' => 'application/json',
            ],
        ])->getBody()->getContents();
        if ($user_id == null || $user_id == 0) {
            return response()->json('unauthorized', 200);
        }

        $product = Product::findOrFail($id);
        if ($user_id == $product->user_id) {
            $product->delete();
            return response()->json('successfully deleted product', 200);
        }
        return response()->json('unauthorized', 200);

    }

    public function searchProducts($name)
    {
        $products = Product::with('price')
            ->where('name', 'LIKE', "%{$name}%")
            ->paginate(20);
        return response()->json($products, 200);
    }
}
