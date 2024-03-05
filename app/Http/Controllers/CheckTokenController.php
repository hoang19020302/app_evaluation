<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CheckTokenController extends Controller
{
    // GET check-token?token=
    public function checkToken(Request $request) {
        // Lấy token từ request
        $token = $request->query('token');
        $tokenData = Cache::get($token);
        // Kiểm tra tính hợp lệ của token
        if ($tokenData && now()->lt($tokenData['expiration'])) {
            $classify = $tokenData['classify'];
            //$userId = $tokenData['userId'];
            $url = 'http://localhost:5500/' . $classify;
            Cache::forget($token);
            // Chuyển hướng sang url
            return Redirect::to($url);
        }
        
        // Token không hợp lệ hoặc đã hết hạn, chuyển hướng đến trang thông báo lỗi
        return Redirect::to(route('error'));
    }
}