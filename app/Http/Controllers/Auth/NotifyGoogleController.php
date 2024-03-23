<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotifyGoogleController extends Controller
{
    //GET notify/{state}
    function notifyStatus() {
        return view('google_facebook.handle_notify');
    }
    //GET no-internet
    function noInternet() {
        return view('google_facebook.no_internet');
    }
}