<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait GetUserIdTrait{
    public function getUserId($user_token,$url){
        $headers = [
            'Authorization' => 'Bearer ' . $user_token,
            'Accept' => 'application/json',
        ];
        return Http::withHeaders($headers)->get($url);
    }
}
