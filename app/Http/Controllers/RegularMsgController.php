<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use App\Models\Product;
use App\Models\RegularMessages;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class RegularMsgController extends Controller
{
    public function index(Request $request)
    {
        $user_token = $request->bearerToken();
        if($request->header('user_type') === "user"){
            $client = new Client();
            $get_user_info = $client->get(env('USER_API') . 'get-seller-id', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $user_token,
                    'Accept' => 'application/json',
                ],
            ])->getBody()->getContents();

            $user_info = json_decode($get_user_info);
        }
        $client = new Client();
        $get_user_info = $client->get(env('SELLER_USER_API') . 'get-seller-id', [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_token,
                'Accept' => 'application/json',
            ],
        ])->getBody()->getContents();

        $user_info = json_decode($get_user_info);

        $products = Product::whereHas('messages')->where('user_id', '=', $user_info->id)
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
        if($request->header('user_type') === "user"){
            $get_user_info = $client->get('https://tanjeeb.hrazy.com/get-user-id', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $user_token,
                    'Accept' => 'application/json',
                ],
            ])->getBody()->getContents();
            $user_info = json_decode($get_user_info);
            $all_msg = RegularMessages::where('product_id', '=', $id)
                ->where('user_id', '=', $user_info->id)
                ->orderBy('id')
                ->get();
            $seller_id=$request->header('seller_id');
            $get_user_info =json_decode( $client->get("https://tanjeeb.hrazy.com/get-user-profile/$user_info->id")->getBody()->getContents());
            $get_seller_info =json_decode( $client->get("https://seller-users.hrazy.com/get-user-profile/$seller_id")->getBody()->getContents());
        }else{
            $all_msg = RegularMessages::where('product_id', '=', $id)
                ->where('user_id', '=',$request->user_id )
                ->orderBy('id')
                ->get();
            $get_user_info =json_decode( $client->get("https://tanjeeb.hrazy.com/get-user-profile/$request->user_id")->getBody()->getContents());
        }


        return response()->json(['messages'=>$all_msg,'user_info'=>$get_user_info,'seller_info'=>$get_seller_info]);
    }

    public function store(Request $request)
    {

        $msg = new RegularMessages();


        $user_token = $request->bearerToken();
        $client = new Client();
        if ($request->type_of_user === "true") {
            $get_user_info = $client->get('https://tanjeeb.hrazy.com/get-seller-id', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $user_token,
                    'Accept' => 'application/json',
                ],
            ])->getBody()->getContents();
            $user_info = json_decode($get_user_info);
            $msg->user_id=$user_info->id;
            $msg->sender_type = 0;
        } else {

            $msg->sender_type = 1;
            $msg->user_id=$request->user_id;
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
        $msg->timer_status = 1;

        $msg->status = $request->status;
        $msg->offer_amount = $request->offer_amount;


        $msg->offer_ending_date = $request->offer_ending_date;
        //$msg->offer_ending_date = Carbon::parse($request->offer_ending_date);

        $msg->msg = $request->msg;
        $msg->save();
        if ($request->counter_status == "true") {

            $get_msg = RegularMessages::findOrFail($request->msg_id);
            $get_msg->status = $request->status;
            $get_msg->timer_status = 0;
            $get_msg->save();

        }

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
            ->where('user_id', '=', $sender_email)
            ->orderBy('id')
            ->get();
        return response()->json($all_msg, 200);
    }

    public function messageStatusUdate(Request $request, $id)
    {

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

        $get_user_info = $client->get('https://tanjeeb.hrazy.com/user-info', [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_token,
                'Accept' => 'application/json',
            ],
        ])->getBody()->getContents();

        $user_info = json_decode($get_user_info);

        $all_msg = RegularMessages::where('user_id', '=', $user_info->id)->get()->unique('product_id');


        $temp = collect([]);

        foreach ($all_msg as $item) {
            $product = Product::where('id', '=', $item->product_id)
                ->get();

            $message = RegularMessages::where('product_id', '=', $item->product_id)->latest()->first();

            $seller_name = $client->get('http://seller-users.hrazy.com/user-infos/' . $product[0]->user_id)
                ->getBody()->getContents();
            $user_info = json_decode($seller_name);

            $mgsWithProduct = $product->push($message, $user_info);
            $collection = $temp->push($mgsWithProduct);


            $temp = $collection;

        }

        return $temp;
    }

    public function myOffers(Request $request)
    {
        $user_token = $request->bearerToken();

        $client = new Client();

        $get_user_info = $client->get('https://tanjeeb.hrazy.com/user-info', [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_token,
                'Accept' => 'application/json',
            ],
        ])->getBody()->getContents();

        $user_info = json_decode($get_user_info);
        $getAllOffers=RegularMessages::where('user_email','=',$user_info->email)
            ->where('status','=',1)
            ->get()->unique('product_id');

        $temp = collect();

        foreach ($getAllOffers as $item) {
            $product = Product::where('id', '=', $item->product_id)
                ->with('price','messages')
                ->get();
           // array_push($temp,$product);
            $collection = $temp->push($product);

            //$temp = $collection;

        }

        return response()->json($temp);
    }
}
