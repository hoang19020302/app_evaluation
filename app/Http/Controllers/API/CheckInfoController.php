<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class CheckInfoController extends Controller
{
    //POST check-info
    public function checkInfo(Request $request) {
        // Lấy dữ liệu từ request
        $email = $request->input('email');
        $password = $request->input('$password');

        // So sánh dữ liệu với dữ liệu trong cache
        if ($this->compareWithDataInCache($email, $password)) {
            // Trả về phản hồi thành công
            return response()->json(['success' => true]);
        } else {
            // Trả về phản hồi không thành công
            return response()->json(['failed' => false]);
        }
    }

    private function compareWithDataInCache($email, $password)
    {
        // Lấy dữ liệu từ cache
    $cachedData = Cache::get('users_info', []);

    // Kiểm tra xem dữ liệu trong cache có tồn tại và khớp với dữ liệu từ form không
    if ($cachedData && $cachedData['email'] === $email && $cachedData['password'] === $password) {
        // Dữ liệu từ form khớp với dữ liệu trong cache
        return true;
    } else {
        // Dữ liệu từ form không khớp hoặc không tồn tại trong cache
        return false;
    }
    }
}
