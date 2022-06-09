<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Flash;
use App\Models\OrderItem;
use App\Models\Product;
use App\Traits\GetUserIdTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

use Carbon\Carbon;

class FlashesController extends Controller
{
    use GetUserIdTrait;

    public function allFlashes()
    {
        $flash_products = Product::whereHas('flashes')->with(['price', 'flashes'])
            ->inRandomOrder()
            ->limit(6)
            ->get();
        foreach ($flash_products as $key => $item) {
            $item->price->makeHidden('reserve_price');
            $item->flashes->makeHidden('pivot');
            $item->reviews = Http::get(env('REVIEWS') . "api/product/reviews/{$item->id}")->json();
            $item->flashes[0]->created_at = $item->flashes[0]->created_at->addDays(1);
        }

        return response()->json($flash_products, 200);
    }

    public function getFlashMaxSale()
    {

        $today_sale_avg = OrderItem::whereDate('created_at', Carbon::today())
            ->where('items_status', true)
            ->avg('qnt');
        $flash_products = Product::whereHas('flashes')
            ->join('order_items', 'order_items.product_id', '=', 'products.id')
            ->where('qnt', '>=', $today_sale_avg)
            ->pluck('product_id');
        
        $products = Product::whereIn('id', $flash_products)->with(['price', 'flashes'])->get();
//        dd($products);
//        $sales_avg = Product::all()->avg('total_sales');
//
//        $flash_products = Product::whereHas('flashes')
//            ->with(['price', 'flashes'])
//            ->where('total_sales', '>', $sales_avg)
//            ->inRandomOrder()
//            ->limit(6)
//            ->get();


        foreach ($products as $key => $item) {
            $item->price->makeHidden('reserve_price');
            $item->flashes->makeHidden('pivot');
            $item->reviews = Http::get(env('REVIEWS') . "api/product/reviews/{$item->id}")->json();
            $item->flashes[0]->created_at = $item->flashes[0]->created_at->addDays(1);
        }

        return response()->json($products, 200);
    }

    public function hotDeals()
    {
        $flashesAvg = Flash::all()->avg('discount');

        $products = Product::whereHas('flashes')->with(['price', 'flashes'])->inRandomOrder()->get();

        $getMaxFlashProducts = $products->filter(function ($product) use ($flashesAvg) {
            return $product->flashes->avg('discount') >= $flashesAvg;
        })->values();
        foreach ($getMaxFlashProducts as $item) {
            $item->price->makeHidden('reserve_price');
            $item->flashes->makeHidden('pivot');
            $item->reviews = Http::get(env('REVIEWS') . "api/product/reviews/{$item->id}")->json();
            $item->category = Category::where("id", $item->category_id)->pluck("name");
            $item->flashes[0]->created_at = $item->flashes[0]->created_at->addDays(1);
        }

        return response()->json($getMaxFlashProducts, 200);
    }

    public function getAllFlashes()
    {

        $products = Product::whereHas('flashes')->with(['price', 'flashes'])->inRandomOrder()->paginate(20);

        foreach ($products as $item) {
            $item->price->makeHidden('reserve_price');
            $item->reviews = Http::get(env('REVIEWS') . "api/product/reviews/{$item->id}")->json();
            $item->flashes->makeHidden('pivot');
        }

        return response()->json($products, 200);
    }

    public function allFlashesTest()
    {
        $flash_products = Product::whereHas('flashes')->with(['price', 'flashes'])
            ->inRandomOrder()
            ->limit(6)
            ->get();
        foreach ($flash_products as $item) {
            $item->price->makeHidden('reserve_price');
            $item->flashes->makeHidden('pivot');

            $item->flashes[0]->created_at = $item->flashes[0]->created_at->addDays(1);
        }

        return response()->json($flash_products, 200);
    }

    public function index(Request $request)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);
        if ($user_id->status() == 500) {
            return response()->json('unauthorized', 200);
        }

        $flashes = Flash::where('seller_id', $user_id)->get();
        return response()->json($flashes, 200);
    }

    public function show(Request $request, $id)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);

        if ($user_id->status() == 500) {
            return response()->json('unauthorized', 200);
        }

        $flash = Flash::findOrFail($id);
        return response()->json($flash, 200);
    }

    public function store(Request $request)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);
        if ($user_id->status() == 400) {
            return response()->json('unauthorized', 200);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:flashes|max:255',
            'discount' => 'required|max:255'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $flash = new Flash();
        $data = $request->all() + array("seller_id" => $user_id->json());
        $flash->create($data);
        return response()->json('successfully save promotional offer.', 200);
    }

    public function update(Request $request, $id)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);
        if ($user_id->status() == 400) {
            return response()->json('unauthorized', 200);
        }
        $flash = Flash::findOrFail($id);
        if ($user_id == $flash->seller_id) {
            $flash->update($request->all());
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

        $flash = Flash::findOrFail($id);
        if ($user_id == $flash->seller_id) {
            $flash->delete();
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
            $flash = Flash::findOrFail($request->flash_id);
            $product->flashes()->save($flash);
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

        return Product::whereHas('flashes', function ($query) use ($id) {
            $query->where('flash_id', $id);
        })->where('user_id', $user_id)->paginate(20);
    }

    public function getProductForPromotion(Request $request)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);
        if ($user_id->status() == 400) {
            return response()->json('unauthorized', 200);
        }
        return Product::doesntHave('flashes')->where('user_id', $user_id)->with('price')->paginate(20);
    }

    public function detachProduct(Request $request, $product_id, $flash_id)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);
        if ($user_id->status() == 400) {
            return response()->json('unauthorized', 200);
        }
        $product = Product::whereHas('flashes', function ($query) use ($product_id, $flash_id) {
            $query->where('flash_id', $flash_id)->where('product_id', $product_id);
        })->first();

        if ($product) {
            $product->flashes()->detach($flash_id);
            return response()->json('successfully remove product from flash');
        }
        return response()->json('This product do not attached with this flash');

    }
}
