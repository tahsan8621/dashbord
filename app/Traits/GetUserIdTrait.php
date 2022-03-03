<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait GetUserIdTrait{
    public function getUserId($user_token,$url){
        $headers = [
            'Authorization' => 'Bearer ' . $user_token,
            'Accept' => 'application/json',
        ];
        $user_info=Http::withHeaders($headers)->get($url);
        if ($user_info->status() == 401) {
            return response()->json('unauthorized', 200);
        }
        return $user_info;
    }
}
