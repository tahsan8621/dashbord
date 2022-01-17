<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;



class CartsController extends Controller
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


    public function store(Request $request)
    {
        dd($request->all());
        return response()->json("success", 200);
    }


}
