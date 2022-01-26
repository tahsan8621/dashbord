<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\RegularMessages;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RegularMsgController extends Controller
{
    public function index(Request $request)
    {
        $user_token = $request->bearerToken();

        $client = new Client();
        $get_user_info = $client->get(env('USER_API_BASE') . 'get-seller-id', [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_token,
                'Accept' => 'application/json',
            ],
        ])->getBody()->getContents();
        $user_info = json_decode($get_user_info);

        $products = Product::whereHas('messages')->where('user_id','=',$user_info->id)
            ->with('price')->get();
        //$products = Product::whereHas('messages')->with('price')->get();
        //$products = Product::where('user_id','=',$user_info->id)->whereHas('messages')->with('price')->get();
        //$products = Product::where('user_id','=',$user_info->id)->get()->whereHas('messages');

        return response()->json($products, 200);
    }

    public function show(Request $request, $id)
    {

        $user_token = $request->bearerToken();

        $client = new Client();

        $get_user_info = $client->get('http://tanjeeb.hrazy.com/get-seller-id', [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_token,
                'Accept' => 'application/json',
            ],
        ])->getBody()->getContents();
        $user_info = json_decode($get_user_info);
//        dd($user_info);
        //dd($request->header('seller_email'));
        if($request->header('user_type')==="user"){

            $all_msg = RegularMessages::where('product_id', '=', $id)
               ->where('user_email', '=', $user_info->email)
                ->orderBy('id')
                ->get();
        }else{
            $all_msg = RegularMessages::where('product_id', '=', $id)
                ->where('user_email', '=', $user_info->email)
                ->orderBy('id')
                ->get();
        }

        return response()->json($all_msg, 200);
    }

    public function store(Request $request)
    {
        $msg = new RegularMessages();

        $user_token = $request->bearerToken();
        $client = new Client();
        if ($request->type_of_user === "true") {
            $get_user_info = $client->get('http://tanjeeb.hrazy.com/get-seller-id', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $user_token,
                    'Accept' => 'application/json',
                ],
            ])->getBody()->getContents();
            $user_info = json_decode($get_user_info);
            $msg->user_email = $user_info->email;
            $msg->sender_type = 0;
        } else {
            $msg->sender_type = 1;
            $msg->user_email = $request->to_email;
        }

        if ($request->file('image')) {
            $image_data = $request->file('image');
            $photo_path = $image_data;

            $m_path = time() . $photo_path->getClientOriginalName();


            $photo_path->move('images/msg', $m_path);
            $msg->image = env('APP_URL') . '/images/msg/' . $m_path;
        }
        $msg->product_id = $request->product_id;
        $msg->seller_id = $request->seller_id;


        $msg->status = $request->status;
        $msg->offer_amount = $request->offer_amount;


        $s = '06/10/2011 19:00:02';
        $date = strtotime($s);
        echo date('d/M/Y H:i:s', $date);
        $msg->offer_ending_date = $request->offer_ending_date;

        $msg->msg = $request->msg;
        $msg->save();
        return response()->json('successfully send message', 200);

    }

    public function getUsers($product_id)
    {

        $messages = RegularMessages::where('product_id', '=', $product_id)->latest()->get();
        $usersUnique = $messages->unique('user_email');
        return response()->json($usersUnique, 200);
    }

    public function getUserMsg($product_id, $sender_email)
    {
        $all_msg = RegularMessages::where('product_id', '=', $product_id)
            ->where('user_email', '=', $sender_email)
            ->orderBy('id')
            ->get();
        return response()->json($all_msg, 200);
    }

    public function messageStatusUdate(Request $request, $id)
    {
//        dd($request->status);
        $msg = RegularMessages::findOrFail($id);
        $msg->status = $request->status;
        if ($msg->save()) {
            return response()->json('successfully message status updated', 200);
        }
        return response()->json('message status not updated', 301);
    }

    public function userMsgs(Request $request)
    {
        $user_token = $request->bearerToken();

        $client = new Client();

        $get_user_info = $client->get('http://tanjeeb.hrazy.com/user-info', [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_token,
                'Accept' => 'application/json',
            ],
        ])->getBody()->getContents();

        $user_info = json_decode($get_user_info);

        $all_msg = RegularMessages::where('user_email', '=', $user_info->email)->get()->unique('product_id');
//        dd($all_msg);

        $temp=collect([]);

        foreach ($all_msg as $item){
            $product=Product::where('id','=',$item->product_id)
                ->get();

            $message=RegularMessages::where('product_id','=',$item->product_id)->latest()->first();
//            dd($product);
            $seller_name=$client->get('http://seller-users.hrazy.com/user-infos/'.$product[0]->user_id)
                ->getBody()->getContents();
            $user_info = json_decode($seller_name);

            $mgsWithProduct=$product->push($message, $user_info );
            $collection = $temp->push($mgsWithProduct);

            //dd($seller_name);
            $temp=$collection;

        }




       // return $collection;
        return $collection;
    }
}
