<?php

namespace App\Http\Controllers;

use App\Models\Flash;
use App\Traits\GetUserIdTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FlashesController extends Controller
{
    use GetUserIdTrait;

    public function index(Request $request)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);

        $flashes = Flash::where('seller_id', $user_id)->get();
        return response()->json($flashes, 200);
    }

    public function show(Request $request, $id)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);

        $flash = Flash::findOrFail($id);
        return response()->json($flash, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:promotions|max:255',
            'discount' => 'required|max:255'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }


        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);
        $flash = new Flash();
        $data = $request->all() + array("seller_id" => $user_id->json());
        $flash->create($data);
        return response()->json('successfully save promotional offer.', 200);
    }

    public function update(Request $request,$id)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);
        $flash=Flash::findOrFail($id);
        if($user_id==$flash->seller_id){
            $flash->update($request->all());
            return response()->json('successfully updated promotion', 200);
        }

        return response()->json('unauthorized', 200);
    }

    public function destroy(Request $request, $id)
    {
        $url = env('SELLER_USER_API') . 'user';
        $user_id = $this->getUserId($request->bearerToken(), $url);

        $flash = Flash::findOrFail($id);
        if ($user_id == $flash->seller_id) {
            $flash->delete();
            return response()->json('successfully deleted promotion', 200);
        }
        return response()->json('unauthorized', 200);

    }
}
