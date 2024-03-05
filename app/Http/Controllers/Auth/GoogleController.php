<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    //GET auth/register
    public function redirectToGoogleForRegister() {
        return Socialite::driver('google')
        ->with(['state' => 'register', 'prompt' => 'consent'])//'prompt' => 'select_account', 'access_type' => 'offline', hd: 'vnu.edu.vn',
        ->redirect();
    }

     // GET auth/login
     public function redirectToGoogleForLogin() {
        return Socialite::driver('google')
        ->with(['state' => 'login', 'prompt' => 'select_account'])
        ->redirect();
     }

     // GET auth/forgot-password
     public function forgotPasswordGoogle() {
        return Socialite::driver('google')
        ->with(['state' => 'forgot-password', 'prompt' => 'select_account'])
        ->redirect();
     }

    // GET auth/google/callback
    public function handleGoogleCallback(Request $request) {
        // Lấy state
        $state = $request->query('state');
        $googleUser = Socialite::driver('google')->stateless()->user();
        // Lấy thông tin người dùng google
        if ($state === 'register') {
            // Lưu thông tin người dùng vào cache
            $userCacheKey = 'google_user_' . Str::random(40); // Tạo một key ngẫu nhiên cho cache
            Cache::put($userCacheKey, $googleUser, now()->addMinutes(10)); // Lưu thông tin người dùng trong 10 phút
        
            // Đăng nhập người dùng vào hệ thống
            Auth::loginUsingId($userCacheKey);
        
            // Chuyển hướng người dùng sau khi đăng nhập thành công
            return redirect()->route('home')->with('state', $state);

        } elseif ($state === 'login') {
             // Lưu thông tin người dùng vào cache
            $userCacheKey = 'google_user_' . Str::random(40); // Tạo một key ngẫu nhiên cho cache
            Cache::put($userCacheKey, $googleUser, now()->addMinutes(10)); // Lưu thông tin người dùng trong 10 phút
        
             // Đăng nhập người dùng vào hệ thống
            Auth::loginUsingId($userCacheKey);
        
             // Chuyển hướng người dùng sau khi đăng nhập thành công
            return redirect()->route('home')->with('state', $state);
         } elseif ($state === 'forward-password') {
            // Lưu thông tin người dùng vào cache
            $userCacheKey = 'google_user_'. Str::random(40); // Tạo một key ngẫu nhiên cho cache
            Cache::put($userCacheKey, $googleUser, now()->addMinutes(10)); // Lưu thông tin người dùng trong 10 phút
            // Đăng nhập người dùng vào hệ thống
            Auth::loginUsingId($userCacheKey);
        
             // Chuyển hướng người dùng sau khi đăng nhập thành công
            return redirect()->route('home')->with('state', $state);
         }
    }
}
