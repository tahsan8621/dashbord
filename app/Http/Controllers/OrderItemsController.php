<?php

namespace App\Http\Controllers;


use App\Models\OrderItem;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;


class OrderItemsController extends Controller
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

    public function show($id)
    {
        //dd($id);
        try {
            $orders = OrderItem::where('order_id','=', $id)
                ->join('products','products.id', '=', 'product_id')
                ->join('price_product', 'price_product.product_id', '=', 'products.id')
                ->get();
            return response()->json($orders, 200);
        }catch (\Exception $e){
            return response()->json('invalid order id '.$id, 200);
        }

    }

    public function update(Request $request,$orderId,$productId)
    {
        $order_item_id=OrderItem::where('order_id','=',$orderId)->where('product_id','=',$productId)
            ->pluck('id');
        $orderItem=OrderItem::find($order_item_id)->first();
        $orderItem->qnt=$request->qnt;
        if($orderItem->save()){
            return response()->json('quantity successfully update',200);
        }
        return response()->json('quantity doesn\'t update',200);
    }

    public function statusUpdate(Request $request, $orderId,$productId)
    {
       // dd($request->status);
        $order_item_id=OrderItem::where('order_id','=',$orderId)->where('product_id','=',$productId)
            ->pluck('id');
        $orderItem=OrderItem::find($order_item_id)->first();
        $orderItem->status=$request->status;
        if($orderItem->save()){
            return response()->json('Status successfully update',200);
        }
        return response()->json('Status doesn\'t update',404);
    }

    public function orderDetails($order_id)
    {

        $order_details=OrderItem::where('order_id','=',$order_id)
            ->join('products','products.id', '=', 'product_id')
            ->get();
        return response()->json($order_details,200);
    }
}
