<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class LogoutController extends Controller
{
    //POST api/logout
    public function logoutUser(Request $request) {
        //Lấy token từ header
        $token = $request->bearerToken();
        $expiration = Carbon::now()->addDays(30)->diffInSeconds();
        // Lấy list user từ cache
        $users = Cache::get('users_info', []);
        // Tìm kiếm người dùng trong danh sách
        foreach ($users as $user) {
            if ($user['token'] === $token) {
                $user['token'] = '';
                break;
            }
        }
        // Lưu lại danh sach vào cache
        Cache::put('users_info', $users, $expiration);

        return response()->json(['success' => 'User logged out successfully'], 200);
    }
}
