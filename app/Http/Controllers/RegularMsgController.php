<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\RegularMessages;
use Illuminate\Http\Request;

class RegularMsgController extends Controller
{
    public function index()
    {
        $products = Product::whereHas('messages')->with('price')->get();

        return response()->json($products, 200);
    }

    public function show($id)
    {
        $all_msg = RegularMessages::where('product_id', '=', $id)
            ->where('sender_email','=','user@shoppee.com')
            ->orWhere('to_email','=','user@shoppee.com')
            ->orderBy('id')
            ->get();
        return response()->json($all_msg, 200);
    }

    public function store(Request $request)
    {
        $msg = new RegularMessages();

        if ($request->file('image')) {
            $image_data = $request->file('image');
            $photo_path = $image_data;

            $m_path = time() . $photo_path->getClientOriginalName();


            $photo_path->move('images/msg', $m_path);
            $msg->image = env('API_PATH') . 'images/msg/' . $m_path;
        }
        $msg->product_id = $request->product_id;
        $msg->sender_email = $request->sender_email;
        $msg->to_email = $request->to_email;
        $msg->status= $request->status;
        $msg->offer_amount= $request->offer_amount;
        $msg->offer_ending_date= $request->offer_ending_date;

        $msg->msg = $request->msg;
        $msg->save();
        return response()->json('successfully send message', 200);

    }

    public function getUsers($product_id)
    {

        $messages = RegularMessages::where('product_id','=',$product_id)->latest()->get();
        $usersUnique = $messages->unique('sender_email');
        return response()->json($usersUnique,200);
    }

    public function getUserMsg($product_id,$sender_email)
    {
        $all_msg = RegularMessages::where('product_id', '=', $product_id)
            ->where('sender_email','=',$sender_email)
            ->orWhere('to_email','=',$sender_email)
            ->orderBy('id')
            ->get();
        return response()->json($all_msg, 200);
    }

    public function messageStatusUdate(Request $request,$id)
    {
//        dd($request->status);
            $msg=RegularMessages::findOrFail($id);
            $msg->status= $request->status;
            if($msg->save()){
                return response()->json('successfully message status updated', 200);
            }
        return response()->json('message status not updated', 301);
    }
}
