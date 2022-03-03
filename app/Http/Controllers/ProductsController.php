<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Price;
use App\Models\Product;
use App\Models\Value;
use App\Traits\GetUserIdTrait;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Console\Input\Input;


class ProductsController extends Controller
{
    use GetUserIdTrait;

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
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($user_token, $url);

        if ($user_id->status() == 401) {
            return response()->json('unauthorized', 200);
        }

        return Product::with('price')
            ->where('user_id', '=', $user_id->json())
            ->latest()
            ->paginate(20);
    }

    public function categorySearch($cat_id)
    {
        $min = 60 * 600;

        $products = Cache::remember("$cat_id", $min, function () use ($cat_id) {
            $products = Product::where('category_id', $cat_id)->with('price')->latest()->paginate(20);
            foreach ($products as $product) {
                $product->price->makeHidden('reserve_price');
                $product->reviews = Http::get(env('REVIEWS') . "api/product/reviews/{$product->id}")->json();
            }
            return $products;
        });
        return response($products, 200);
    }

    public function allProducts()
    {
        $min = 60 * 600;
        $currentPage = request()->get('page', 1);
        $data = Cache::remember('products' . '_page_' . $currentPage, $min, function () {
            $products = Product::with('price')
                ->latest()
                ->paginate(20);

            foreach ($products as $product) {
                $product->price->makeHidden('reserve_price');
                $product->reviews = Http::get(env('REVIEWS') . "api/product/reviews/{$product->id}")->json();
            }
            return $products;
        });
        return response($data, 200);
    }

    public function show($id)
    {
        $min = 60 * 600;

        $product = Cache::remember("product_$id", $min, function () use ($id) {
            $product = Product::findOrFail($id);
            $product->reviews = Http::get(env('REVIEWS') . "api/product/{$id}/reviews")->json();
            return $product;
        });
        $seller_infos = Cache::remember("$product->user_id", $min, function () use ($product) {
            Http::get(env('SELLER_USER_API') . "user-infos/{$product->user_id}")->json();
        });

        $attrs = Cache::remember("$id", $min, function () use ($id) {
            Attribute::with('values')
                ->where('product_id', '=', $id)
                ->get();
        });
        $prices = Price::where('product_id', '=', $id)->get(['bidding_time', 'starting_price', 'buy_now_price']);


        return response()->json(['products' => $product, 'attributes' => $attrs, 'prices' => $prices, 'seller_infos' => $seller_infos], 200);
    }

    public function showEdit(Request $request, $id)
    {

        $user_token = $request->bearerToken();
        $url = env('SELLER_USER_API') . 'user';
        $user_info = $this->getUserId($user_token, $url);
        if ($user_info->status() == 401) {
            return response()->json('unauthorized user', 401);
        }
        $user_id = $user_info->json();
        $products = Product::findOrFail($id);
        $attrs = Attribute::with('values')
            ->where('product_id', '=', $id)
            ->get();
        $prices = Price::where('product_id', '=', $id)->get();
        if ($products->user_id == $user_id) {
            return response()->json(['products' => $products, 'attributes' => $attrs, 'prices' => $prices], 200);
        }
        return response()->json('product doesn\'t found', '401');
    }

    public function store(Request $request)
    {
        $user_token = $request->bearerToken();
        $client = new Client();

        $user_id = $client->get(env('SELLER_USER_API') . 'user', [
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
            $product->image = env('APP_URL') . "/public/images/products/" . $m_path;
        }
        if ($request->file('image_1')) {
            $photo_path = $request->file('image_1');
            $m_path = time() . $photo_path->getClientOriginalName();

            $photo_path->move('images/products', $m_path);
            $product->image_1 = env('APP_URL') . "/public/images/products/" . $m_path;
        }
        if ($request->file('image_2')) {
            $photo_path = $request->file('image_2');
            $m_path = time() . $photo_path->getClientOriginalName();
            $photo_path->move('images/products', $m_path);
            $product->image_2 = env('APP_URL') . "/public/images/products/" . $m_path;
        }
        if ($request->file('image_3')) {
            $photo_path = $request->file('image_3');
            $m_path = time() . $photo_path->getClientOriginalName();
            $photo_path->move('images/products', $m_path);
            $product->image_3 = env('APP_URL') . "/public/images/products/" . $m_path;
        }
        if ($request->file('image_4')) {
            $photo_path = $request->file('image_4');
            $m_path = time() . $photo_path->getClientOriginalName();
            $photo_path->move('images/products', $m_path);
            $product->image_4 = env('APP_URL') . "/public/images/products/" . $m_path;
        }
        $product->name = $request->name;
        $product->sku = $request->sku;
        $product->brand_name = $request->brand_name;
        $product->product_type = $request->product_type;
        $product->total_products = $request->total_products;
        $product->description = $request->description;
        $product->total_sales = 0;
        $product->status = $request->status;
        $product->user_id = $user_id;
        $product->category_id = $request->category_id;

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

        $user_id = $client->get(env('SELLER_USER_API') . 'user', [
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
            if ($request->file('image')) {
                $photo_path = $request->file('image');
                $m_path = time() . $photo_path->getClientOriginalName();
                $photo_path->move('images/products', $m_path);
                $product->image = env('APP_URL') . "/public/images/products/" . $m_path;
            }
            if ($request->file('image_1')) {
                $photo_path = $request->file('image_1');
                $m_path = time() . $photo_path->getClientOriginalName();

                $photo_path->move('images/products', $m_path);
                $product->image_1 = env('APP_URL') . "/public/images/products/" . $m_path;
            }
            if ($request->file('image_2')) {
                $photo_path = $request->file('image_2');
                $m_path = time() . $photo_path->getClientOriginalName();
                $photo_path->move('images/products', $m_path);
                $product->image_2 = env('APP_URL') . "/public/images/products/" . $m_path;
            }
            if ($request->file('image_3')) {
                $photo_path = $request->file('image_3');
                $m_path = time() . $photo_path->getClientOriginalName();
                $photo_path->move('images/products', $m_path);
                $product->image_3 = env('APP_URL') . "/public/images/products/" . $m_path;
            }
            if ($request->file('image_4')) {
                $photo_path = $request->file('image_4');
                $m_path = time() . $photo_path->getClientOriginalName();
                $photo_path->move('images/products', $m_path);
                $product->image_4 = env('APP_URL') . "/public/images/products/" . $m_path;
            }
            $product->name = $request->name;
            $product->sku = $request->sku;
            $product->product_type = $request->product_type;
            $product->total_products = $request->total_products;
            $product->description = $request->description;
            $product->total_sales = $request->total_sales;
            $product->save();
            $att_array = json_decode($request->new_attribute);

            if ($att_array) {
                $count = count($att_array);
                for ($limit = 0; $limit < $count; $limit++) {
                    $attr = Attribute::findOrFail($att_array[$limit]->id);
                    $attr->name = $att_array[$limit]->name;
                    $attr->product_id = $att_array[$limit]->product_id;

                    $attr->save();
                    $count_values = count($att_array[$limit]->values);

                    for ($i = 0; $i < $count_values; $i++) {

                        $values = Value::findOrFail($att_array[$limit]->values[$i]->id);

                        $values->value_name = $att_array[$limit]->values[$i]->value_name;
                        $values->attribute_id = $attr->id;
                        $values->value_price = $att_array[$limit]->values[$i]->value_price;
                        $values->save();
                    }
                }
            }
            return response()->json("successfully update your product", 200);
        }
        return response()->json('unauthorized', 200);
    }

    public function destroy(Request $request, $id)
    {
        $user_token = $request->bearerToken();

        $client = new Client();

        $user_id = $client->get(env('SELLER_USER_API') . 'user', [
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
        $searchValues = preg_split('/\%20+/', $name, -1, PREG_SPLIT_NO_EMPTY);
        $products = Product::where(function ($q) use ($searchValues) {
            foreach ($searchValues as $value) {
                $q->Where('name', 'like', "%{$value}%");
            }
        })->with('price')->paginate(20);
        foreach ($products as $item) {
            $item->price->makeHidden('reserve_price');
        }
        return response()->json($products, 200);
    }

    public function productReservedPriceById(Request $request, $id)
    {
        if ($request->header('token') === 'mr-place') {
            $product = Price::where('product_id', '=', $id)->pluck('reserve_price');
            return response()->json($product);
        }
        return response()->json('you are not authorized');
    }

    public function bidProducts($id)
    {
        $product = Product::select('name', 'image')
            ->where('id', '=', $id)
            ->get();
        $newCollections = collect();
        $price = Product::with('price')
            ->find($id)
            ->price
            ->makeHidden(['reserve_price', "id", 'starting_price', 'buy_now_price', 'product_id', 'created_at', 'updated_at']);
        $product->push($price);
        $newCollections->push($product);
        $test = Product::with('price')->find($id);
        $test->price->makeHidden(['reserve_price', "id", 'starting_price', 'buy_now_price', 'product_id', 'created_at', 'updated_at']);

        return $test;
    }

    public function searchKeys($name)
    {
        $searchValues = preg_split('/\%20+/', $name, -1, PREG_SPLIT_NO_EMPTY);
        $keys = Product::where(function ($q) use ($searchValues) {
            foreach ($searchValues as $value) {
                $q->Where('name', 'like', "%{$value}%");
            }
        })->select(['category_id', 'id'])->take(10)->get();

        $productWithCat = collect();
        foreach ($keys as $key => $item) {
            $product = Product::where('id', $item->id)->select('name')->get()->first();
            $category = Category::where('id', $item->category_id)->select('name')->get()->first();
            $product->setAttribute('category', $category);

            $collection = $productWithCat->push($product);
            $productWithCat = $collection;
        }

        return $productWithCat;
    }


}
