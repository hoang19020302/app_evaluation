<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class RegisterController extends Controller
{
    //POST users-info
    public function registerUser(Request $request) {
        // Logic xử lý đăng ký người dùng
        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        // Tạo một id mới cho người dùng
        $id = Str::uuid().substr(0, 8);
        // Thoi gian 30 ngay
        $expiration = Carbon::now()->addDays(30)->diffInSeconds();
        // Tạo token cho người dùng
        $token = Str::random(60);

        // Lưu token và thông tin người dùng vào cache
        $userInfo = [
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'token' => $token,
        ]; 

        $users = Cache::get('users_info', []);


        // Kiểm tra xem email đã tồn tại trong danh sách người dùng chưa
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                return response()->json(['error' => 'Email already exists'], 400);
            }
        }
        // Tạo  admin
        $isAdmin = $email === 'tranichhoang2001@gmail.com' ? true : false;
        $userInfo['is_admin'] = $isAdmin;
        // Thêm thông tin người dùng mới vào danh sách
        $users[] = $userInfo;

        // Lưu danh sách người dùng mới vào cache
        Cache::put('users_info', $users, $expiration);

        return response()->json(['success' => 'User registered successfully', 'token' => $token, 'id' => $id], 200);
    }
}
