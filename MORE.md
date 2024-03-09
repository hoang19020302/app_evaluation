mail/EvaluationInvitation.php  
Middleware/CacheTokenAuthMiddleware.php, Middleware/CheckTokenExpiration.php, Middleware/Cors.php  
app/http/Controller/API  
resource/views/check  
.env(smtp, database-navicat)  
php artisan migrate, php artisan make:migration create_images_table(email, image)  




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

    // GET auth/google/callback
    public function handleGoogleCallback(Request $request) {
        // Lấy state
        $state = $request->query('state');
        $googleUser = Socialite::driver('google')->stateless()->user();
        // Lấy thông tin người dùng google
        $email = $googleUser->getEmail();
        $name = $googleUser->getName();
        $avatar = $googleUser->getAvatar();
        $birthday = $googleUser->user['birthday'] ?? null;

        if ($state === 'register') {
            // Lưu thông tin người dùng vào cache
            $userCacheKey = 'google_user_' . Str::random(40); // Tạo một key ngẫu nhiên cho cache
            Cache::put($userCacheKey, $googleUser, now()->addMinutes(10)); // Lưu thông tin người dùng trong 10 phút
            
            // Đăng nhập người dùng vào hệ thống
            Auth::loginUsingId($userCacheKey);
        
            // Chuyển hướng người dùng sau khi đăng nhập thành công
            return redirect()->route('home')->with(['state' => $state, 'email' => $email, 'name' => $name, 'birthday' => $birthday]);

        } elseif ($state === 'login') {
             // Lưu thông tin người dùng vào cache
            $userCacheKey = 'google_user_' . Str::random(40); // Tạo một key ngẫu nhiên cho cache
            Cache::put($userCacheKey, $googleUser, now()->addMinutes(10)); // Lưu thông tin người dùng trong 10 phút
        
             // Đăng nhập người dùng vào hệ thống
            Auth::loginUsingId($userCacheKey);
        
             // Chuyển hướng người dùng sau khi đăng nhập thành công
            return redirect()->route('home')->with(['state' => $state, 'email' => $email, 'avatar' => $avatar]);
         } elseif ($state === 'forgot-password') {
            // Lưu thông tin người dùng vào cache
            $userCacheKey = 'google_user_' . Str::random(40); // Tạo một key ngẫu nhiên cho cache
            Cache::put($userCacheKey, $googleUser, now()->addMinutes(10)); // Lưu thông tin người dùng trong 10 phút
        
             // Đăng nhập người dùng vào hệ thống
            Auth::loginUsingId($userCacheKey);
        
             // Chuyển hướng người dùng sau khi đăng nhập thành công
            return redirect()->route('home')->with(['state' => $state, 'email' => $email, 'avatar' => $avatar]);
         }
    }
}