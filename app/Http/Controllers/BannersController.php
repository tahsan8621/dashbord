<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Support\Facades\Validator;
use App\Traits\GetUserIdTrait;
use Illuminate\Http\Request;

class BannersController extends Controller
{
    use GetUserIdTrait;

    public function index(Request $request)
    {
        $user_token = $request->bearerToken();
        $url = env('USER_API') . 'user';
        $res = $this->getUserId($user_token, $url);
        if (isset($res->original)) {
            return response()->json('unauthorized', 401);
        }
        $banners = Banner::where('seller_id', $res->body())->get();
        return response()->json($banners, 200);
    }

    public function show($id)
    {
        return 0;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:banners|max:255',
            'image' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $user_token = $request->bearerToken();
        $url = env('USER_API') . 'user';
        $res = $this->getUserId($user_token, $url);
        if (isset($res->original)) {
            return response()->json('unauthorized', 401);
        }

        $photo_path = $request->file('image');
        $m_path = time() . $photo_path->getClientOriginalName();
        $photo_path->move('images/shop/banner', $m_path);

         Banner::create([
            'name' => $request->name,
            'image' => env('APP_URL') . "/public/images/shop/banner/" . $m_path
        ]);


//        if ($request->has('images')) {
//            foreach ($request->file('images') as $image) {
//                $m_path = time() . $image->getClientOriginalName();
//                $image->move('images/slider', $m_path);
//                Image::create([
//                    'image' => env('APP_URL') . "/public/images/slider/" . $m_path,
//                    'slider_id' => $new_slider->id
//                ]);
//            }
//        }

        return response()->json('successfully added');
    }

    public function update(Request $request, $id)
    {
        $user_token = $request->bearerToken();
        $url = env('USER_API') . 'user';
        $res = $this->getUserId($user_token, $url);
        if (isset($res->original)) {
            return response()->json('unauthorized', 401);
        }
        $banner = Banner::find($id);
        if ($banner) {
            if ($banner->seller_id == $res->body()) {

                $banner->update([
                    'name' => $request->name,
                    'seller_id' => $res->body()
                ]);
//                if ($request->has('images')) {
//                    foreach ($request->file('images') as $image) {
//                        $m_path = time() . $image->getClientOriginalName();
//                        $image->move('images/slider', $m_path);
//                        Image::update([
//                            'image' => env('APP_URL') . "/public/images/slider/" . $m_path,
//                        ]);
//                    }
//                }
                return response()->json('successfully updated');
            }
            return response()->json('you are not authorized to delete this item');
        } else {
            return response()->json('unauthorized', 401);
        }
    }

    public function distroy(Request $request, $id)
    {
        $user_token = $request->bearerToken();
        $url = env('USER_API') . 'user';
        $res = $this->getUserId($user_token, $url);
        if (isset($res->original)) {
            return response()->json('unauthorized', 401);
        }
        $banner = Banner::find($id);

        if ($banner) {
            if ($banner->seller_id == $res->body()) {
                $banner->images()->delete();
                $banner->delete();
                return response()->json('successfully deleted');
            }
            return response()->json('you are not authorized to delete this item');
        } else {
            return response()->json('unauthorized', 401);
        }
    }


}
