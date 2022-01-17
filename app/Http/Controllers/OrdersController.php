<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;



class OrdersController extends Controller
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
        $orders=Order::all();
        return response()->json($orders,200);
    }
    public function show($id)
    {
        try {
            $order=Order::findOrfail($id);
            return response()->json($order,200);
        } catch (\Exception $e) {
            return \response()->json( ['errorMsg' => 'Order Id not found'],404);
        }

    }

    public function store(Request $request)
    {

        $order=new Order();
        $orderArray = $request->all();
        $total_price=0;
        if(is_array($orderArray)) {
            foreach ($orderArray as $data) {
                $individual=$data['price']* $data['cartItem_num'];
                $total_price +=$individual;
            }
        }
        //$name = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $order->grand_total = $total_price;
        $order->user_name='Rafayel';
        $order->save();
        $order_id=$order->id;
        if(is_array($orderArray)) {
            foreach ($orderArray as $data) {
                //dd($data['price']);
                $order_item=new OrderItem();
                $order_item->product_id=$data['id'];
                $order_item->order_id=$order_id;
                $order_item->qnt=$data['cartItem_num'];
                $order_item->grand_total=$data['price']* $data['cartItem_num'];
                $order_item->save();
            }
        }


        return response()->json("success", 200);
    }

    public function userOrder($user_name)
    {
        $user_order=Order::where('user_name', $user_name)->with("items")->get();

        return response()->json(['orders'=>$user_order],200);

    }
    public function userOrderOld($user_name)
    {

        $order_id=Order::where('user_name','=',$user_name)->pluck('id');
        $user_order=Order::where('user_name','=',$user_name)->get();
        $user_items=OrderItem::where('order_id','=',$order_id)->get();
        return response()->json(['orders'=>$user_order,'items'=>$user_items],200);
    }

}
