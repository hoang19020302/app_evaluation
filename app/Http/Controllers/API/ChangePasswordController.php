<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class ChangePasswordController extends Controller
{
    // POST api/change-password
    public function changePassword(Request $request) {
        $email = $request->input('email');
        $current_password = $request->input('current_password');
        $new_password = $request->input('new_password');

        // Lấy thông tin user từ cache
        $users = Cache::get('users_info', []);

        // Tìm kiếm user trong danh sách
        foreach ($users as &$user) {
            if ($user['email'] == $email) {
                // Kiểm tra mật khẩu hiện tại
                if ($user['password'] === $current_password) {
                    // Thay đổi mật khẩu mới
                    $user['password'] = $new_password;
                    break;
                } else {
                    return response()->json(['error' => 'Current password is incorrect'], 401);
                }
            }
        }

        // Lưu lại danh sách vào cache
        Cache::put('users_info', $users);

        return response()->json(['success' => 'Password changed successfully'], 200);
    }
}
