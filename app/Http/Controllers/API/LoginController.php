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
        $foundUser = null;
        foreach ($users as &$user) {
            if ($user['token'] === $token) {
                $foundUser = $user;
                break;
            }
        }

    if ($foundUser) {
        return response()->json(['success' => 'User logged in successfully', 'token' => $token, 'id' => $foundUser['id']], 200);
    } else {
        foreach ($users as &$user) {
            if ($user['email'] === $email && $user['password'] === $password) {
                $newToken = Str::random(60);
                $user['token'] = $newToken;
                Cache::put('users_info', $users, $expiration);
                return response()->json(['success' => 'User logged in successfully', 'token' => $newToken, 'id' => $user['id']], 200);
            }
        }
        return response()->json(['error' => 'Login failed'], 401);
    }
    }
}
