<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    //POST api/login
    public function loginUser(Request $request) {
        $email = $request->input('email');
        $password = $request->input('password');
        //Lấy token từ header
        $token = $request->bearerToken();
        $expiration = Carbon::now()->addDays(30)->diffInSeconds();
        
        // Lấy list user từ cache
        $users = Cache::get('users_info', []);
        // Tìm kiếm người dùng trong danh sách
        foreach ($users as $user) {
            if ($user['token'] === $token) {
                return response()->json(['success' => 'User logged in successfully', 'token' => $token, 'id' => $user['id']], 200);
            } elseif ($user['token'] !== $token && $user['email'] === $email && $user['password'] === $password) {
                $newtoken = Str::random(60);
                $user['token'] = $newtoken;
                Cache::put('users_info', $users, $expiration);
                return response()->json(['success' => 'User logged in successfully', 'token' => $newtoken, 'id' => $user['id']], 200);
            } else {
                return response()->json(['error' => 'Login failed'], 401);
            }
        }
    }
}
