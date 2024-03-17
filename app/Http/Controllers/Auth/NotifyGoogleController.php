<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotifyGoogleController extends Controller
{
    //GET notify/{state}
    function notifyStatus() {
        return view('googles.handle_notify');
    }
    //GET no-internet
    function noInternet() {
        return view('googles.no_internet');
    }
}
