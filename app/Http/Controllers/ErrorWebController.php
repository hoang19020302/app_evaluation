<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ErrorWebController extends Controller
{
    //GET erorr
    public function error() {
        return view('error');
    }
}
