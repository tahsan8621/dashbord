<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Slider;
use App\Traits\GetUserIdTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SlidersController extends Controller
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
        $sliders = Slider::where('seller_id', $res->body())->get();
        return response()->json($sliders, 200);
    }

    public function show($id)
    {
        return 0;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:shops|max:255'
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
        $new_slider = Slider::create([
            'name' => $request->name,
            'seller_id' => $res->body()]);

        if ($request->has('images')) {
            foreach ($request->file('images') as $image) {
                $m_path = time() . $image->getClientOriginalName();
                $image->move('images/slider', $m_path);
                Image::create([
                    'image' => env('APP_URL') . "/public/images/slider/" . $m_path,
                    'slider_id' => $new_slider->id
                ]);
            }
        }

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
        $slider = Slider::find($id);
        if ($slider) {
            if ($slider->seller_id == $res->body()) {

                $slider->update([
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
        $slider = Slider::find($id);

        if ($slider) {
            if ($slider->seller_id == $res->body()) {
                $slider->images()->delete();
                $slider->delete();
                return response()->json('successfully deleted');
            }
            return response()->json('you are not authorized to delete this item');
        } else {
            return response()->json('unauthorized', 401);
        }
    }
}
