<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Traits\GetUserIdTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AddressesController extends Controller
{
    use GetUserIdTrait;

    public function userAddresses(Request $request)
    {
        $user_token = $request->bearerToken();
        $url = env('USER_API') . 'user';
        $user_id = $this->getUserId($user_token, $url);

        if ($user_id->status() == 401) {
            return response()->json('unauthorized', 200);
        }
        $userAddress=Address::where('user_id',$user_id->json())->get();



        return response()->json($userAddress,200);
    }
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required',
            'address' => 'required',
            'city' => 'required',
            'postal_code' => 'required',
            'country' => 'required',
            'cell_number' => 'required',
            'title'=>'required|max:255'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $user_token = $request->bearerToken();

        $url = env('USER_API') . 'user';
        $user_id = $this->getUserId($user_token, $url);

        if ($user_id->status() == 401) {
            return response()->json('unauthorized', 200);
        }
        $address=new Address();

        $data=$request->all()+array("user_id" => $user_id->json());

        $address->create($data);
        return response()->json('successfully created Address',200);
    }
}
