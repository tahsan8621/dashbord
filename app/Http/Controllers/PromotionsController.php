<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Traits\GetUserIdTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PromotionsController extends Controller
{
    use GetUserIdTrait;

    public function index(Request $request)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);

        $seller_promotions = Promotion::where('seller_id', $user_id)->get();
        return response()->json($seller_promotions, 200);
    }

    public function show(Request $request, $id)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);

        $promotion = Promotion::findOrFail($id);
        return response()->json($promotion, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:promotions|max:255',
            'discount' => 'required|max:255',
            'starting_time' => 'required',
            'end_time' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }


        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);

        $promotion = new Promotion();
        $data = $request->all() + array("seller_id" => $user_id->json());
        $promotion->create($data);
        return response()->json('successfully save promotional offer.', 200);
    }

    public function update(Request $request,$id)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);
        $promotion=Promotion::findOrFail($id);
        if($user_id==$promotion->seller_id){
            $promotion->update($request->all());
            return response()->json('successfully updated promotion', 200);
        }

        return response()->json('unauthorized', 200);
    }

    public function destroy(Request $request, $id)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);

        $promotion = Promotion::findOrFail($id);
        if ($user_id == $promotion->seller_id) {
            $promotion->delete();
            return response()->json('successfully deleted promotion', 200);
        }
        return response()->json('unauthorized', 200);

    }
}
