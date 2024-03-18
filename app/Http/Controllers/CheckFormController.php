<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CheckFormController extends Controller
{
    //GET erorr
    public function error() {
        return view('check.error', ['status' => 404,'message' => 'Không tìm thấy trang tai khoan chua dang ký']);
    }

    // GET spirit
    public function spirit() {
        return view('check.spirit');
    }

    // GET character
    public function character() {
        return view('check.character');
    }

    //GET failed-login
    public function failedLogin() {
        return view('check.failed_login');
    }
}
