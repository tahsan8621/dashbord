<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\AttributeName;
use App\Models\Brand;
use App\Models\Value;
use GuzzleHttp\Client;
use Illuminate\Http\Request;


class AttributesController extends Controller
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

        $user_id = $client->get('https://seller-users.hrazy.com/user', [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_token,
                'Accept' => 'application/json',
            ],
        ])->getBody()->getContents();
        if ($user_id == null || $user_id == 0) {
            return response()->json('unauthorized', 200);
        }
        $attributes = Attribute::where('user_id','=',$user_id)->get();
        return response()->json($attributes, 200);
    }

    public function show($id)
    {
        $attribute = Attribute::findOrFail($id);
        return response()->json($attribute, 200);
    }

    public function store(Request $request)
    {
        $user_token = $request->bearerToken();

        $client = new Client();

        $user_id = $client->get('https://seller-users.hrazy.com/user', [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_token,
                'Accept' => 'application/json',
            ],
        ])->getBody()->getContents();
        if ($user_id == null || $user_id == 0) {
            return response()->json('unauthorized', 200);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:categories|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $attribute = new Attribute();
        $attribute->name = $request->name;
        $attribute->values = $request->values;
        $attribute->user_id = $user_id;

        $attribute->save();
        return response()->json("success", 200);

    }


    public function createAttr(Request $request)
    {
        $user_token = $request->bearerToken();

        $client = new Client();

        $user_id = $client->get('https://seller-users.hrazy.com/user', [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_token,
                'Accept' => 'application/json',
            ],
        ])->getBody()->getContents();

        if ($user_id == null || $user_id == 0) {
            return response()->json('unauthorized', 200);
        }
        $attr_name = new AttributeName();
        $attr_name->name = $request->name;
        $attr_name->user_id = $user_id;
        $attr_name->save();
        return response()->json("success added your attribute name", 200);
    }
    public function createAttrGet(Request $request)
    {
        $user_token = $request->bearerToken();

        $client = new Client();

        $user_id = $client->get('https://seller-users.hrazy.com/user', [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_token,
                'Accept' => 'application/json',
            ],
        ])->getBody()->getContents();
        if ($user_id == null || $user_id == 0) {
            return response()->json('unauthorized', 200);
        }
        $attr_names = AttributeName::where('user_id','=',$user_id)->get();
        return response()->json($attr_names, 200);
    }
    public function attributeDelete(Request $request,$id)
    {
        $user_token = $request->bearerToken();

        $client = new Client();

        $user_id = $client->get(env('SELLER_USER_API') . 'user', [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_token,
                'Accept' => 'application/json',
            ],
        ])->getBody()->getContents();

        $attr= Attribute::findOrFail($id);

        if ($user_id == $attr->user_id) {
            $attr->delete();
            return response()->json('successfully deleted attribute', 200);
        }
        return response()->json('you are not authorized');
    }

    public function valueDelete($id)
    {
        $value= Value::findOrFail($id);
        $value->delete();
        return response()->json('successfully deleted value', 200);
    }


}
