<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
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

    // Function to handle registration
    protected function handleRegistration() {
        // Your registration logic here
        $googleUser = Socialite::driver('google')->stateless()->user();
        $email = $googleUser->getEmail();
        $name = $googleUser->getName();
        $birthday = $googleUser->user['birthday'] ?? null;
        // Lưu thông tin người dùng vào cache
        $userCacheKey = 'google_user_' . Str::random(40); // Tạo một key ngẫu nhiên cho cache
        Cache::put($userCacheKey, $googleUser, now()->addMinutes(10)); // Lưu thông tin người dùng trong 10 phút
        
        // Đăng nhập người dùng vào hệ thống
        Auth::loginUsingId($userCacheKey);
    
        // Chuyển hướng người dùng sau khi đăng nhập thành công
        return ['email' => $email, 'name' => $name, 'birthday' => $birthday];
    }

    // Function to handle login
    protected function handleLogin() {
        // Your login logic here
        $googleUser = Socialite::driver('google')->stateless()->user();
        $email = $googleUser->getEmail();
        $name = $googleUser->getName();
        $birthday = $googleUser->user['birthday'] ?? null;
        // Lưu thông tin người dùng vào cache
        $userCacheKey = 'google_user_' . Str::random(40); // Tạo một key ngẫu nhiên cho cache
        Cache::put($userCacheKey, $googleUser, now()->addMinutes(10)); // Lưu thông tin người dùng trong 10 phút
        
        // Đăng nhập người dùng vào hệ thống
        Auth::loginUsingId($userCacheKey);
    
        // Chuyển hướng người dùng sau khi đăng nhập thành công
        return ['email' => $email, 'name' => $name, 'birthday' => $birthday];
    }

    // Function to handle forgot password
    protected function handleForgotPassword() {
        // Your forgot password logic here
        $googleUser = Socialite::driver('google')->stateless()->user();
        $email = $googleUser->getEmail();
        $name = $googleUser->getName();
        $birthday = $googleUser->user['birthday'] ?? null;
        // Lưu thông tin người dùng vào cache
        $userCacheKey = 'google_user_' . Str::random(40); // Tạo một key ngẫu nhiên cho cache
        Cache::put($userCacheKey, $googleUser, now()->addMinutes(10)); // Lưu thông tin người dùng trong 10 phút
        
        // Đăng nhập người dùng vào hệ thống
        Auth::loginUsingId($userCacheKey);
    
        // Chuyển hướng người dùng sau khi đăng nhập thành công
        return ['email' => $email, 'name' => $name, 'birthday' => $birthday];
    }

    // GET auth/google/callback
    public function handleGoogleCallback(Request $request) {
        $state = $request->query('state');

        switch ($state) {
            case 'register':
                $userData = $this->handleRegistration();
                return redirect()->route('notify.register')->with(['state' => $state, 'title' => 'Thành công!', 'message' => 'Đăng ký thành công', 'success' => 'alert-success', 'url' => route('home')]);
                //return redirect()->route('home')->with(['state' => $state] + $userData);
                break;
            case 'login':
                $userData = $this->handleLogin();
                //return redirect()->route('notify.login');

                return redirect()->route('home')->with(['state' => $state] + $userData);
                break;
            case 'forgot-password':
                $userData = $this->handleForgotPassword();
                //return redirect()->route('notify.forgot-password');
                return redirect()->route('home')->with(['state' => $state] + $userData);
                break;
            default:
                return redirect()->route('home')->with(['url' => route('welcome')]);
        }
    }
}
