<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\RegularMessages;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class RegularMsgController extends Controller
{
    public function index(Request $request)
    {
        $user_token = $request->bearerToken();

        $client = new Client();
        $get_user_info = $client->get(env('USER_API_BASE').'get-seller-id', [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_token,
                'Accept' => 'application/json',
            ],
        ])->getBody()->getContents();
        $user_info=json_decode($get_user_info);

       dd(Product::whereHas('messages')->with('price')->get());
        //$products = Product::whereHas('messages')->with('price')->get();
        //$products = Product::where('user_id','=',$user_info->id)->whereHas('messages')->with('price')->get();
        $products = Product::where('user_id','=',$user_info->id)->get()->whereHas('messages');

        return response()->json($products, 200);
    }

    public function show(Request $request,$id)
    {
        $user_token = $request->bearerToken();

        $client = new Client();
        $get_user_info = $client->get('http://tanjeeb.hrazy.com/get-seller-id', [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_token,
                'Accept' => 'application/json',
            ],
        ])->getBody()->getContents();
        $user_info=json_decode($get_user_info);
        $all_msg = RegularMessages::where('product_id', '=', $id)
            ->where('sender_email','=',$user_info->email)
            ->orWhere('to_email','=','user@shoppee.com')
            ->orderBy('id')
            ->get();
        return response()->json($all_msg, 200);
    }

    public function store(Request $request)
    {
        $user_token = $request->bearerToken();


        $client = new Client();
        if($request->type_of_user==="true"){
            $get_user_info = $client->get('http://tanjeeb.hrazy.com/get-seller-id', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $user_token,
                    'Accept' => 'application/json',
                ],
            ])->getBody()->getContents();
            $mail_to_info="sendre@sadf.com";
        }else{
            $get_user_info = $client->get(env('USER_API_BASE').'get-seller-id', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $user_token,
                    'Accept' => 'application/json',
                ],
            ])->getBody()->getContents();
            $mail_to_info="sendreFLP@sadf.com";
        }


        $user_info=json_decode($get_user_info);


        $msg = new RegularMessages();

        if ($request->file('image')) {
            $image_data = $request->file('image');
            $photo_path = $image_data;

            $m_path = time() . $photo_path->getClientOriginalName();


            $photo_path->move('images/msg', $m_path);
            $msg->image = env('API_PATH') . 'images/msg/' . $m_path;
        }
        $msg->product_id = $request->product_id;
        $msg->sender_email = $user_info->email;
        $msg->to_email = $mail_to_info;
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
