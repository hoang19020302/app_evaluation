<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotifyGoogleController extends Controller
{
    //GET notify/login
    function notifyLogin() {
        return view('googles.login');
    }

    //GET notify/register
    function notifyRegister() {
        return view('googles.register');
    }

    //GET notify/forgot-password
    function notifyForgotPassword() {
        return view('googles.forgot-password');
    }
}
