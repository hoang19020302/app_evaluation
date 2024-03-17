<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;

class CheckTokenController extends Controller
{
    // GET check-token?token=$token&personality=$personality
    public function checkToken(Request $request) {
        // Lấy token va personality từ request
        $token = $request->query('token');
        $personality = $request->query('personality');

        // Giải mã token
        $decodedToken = Crypt::decryptString($token);
            // Tách thời gian hết hạn và email từ token
        [$expiration, $decodedEmail] = explode('_', $decodedToken);
        // Lấy userId 
        $userId = DB::table('userinfo')->where('email', $decodedEmail)->value('uuid');
        if (empty($userId)) {
            return Redirect::to(route('error'))->with('message', 'Không tìm thấy trang tai khoan chua dang ký');
        } 
        // Kiểm tra xem thời gian hết hạn của token
        if (Carbon::now()->lt($expiration)) {
            // Tạo URL dựa trên personality và email để chuyển hướng
            $url = 'http://localhost:5500/' . $userId . '/' . $personality;
            return Redirect::to($url);
        } else {
            // Token không hợp lệ hoặc đã hết hạn, chuyển hướng đến trang thông báo lỗi
            return Redirect::to(route('error'))->with('message', 'Không tìm thấy trang do link của bạn đã hết hạn.');  
        } 
    }
}

// Lấy userId 
//$userId = DB::table('user')->where('UserName', $email)->value('UserID');
//$url = 'https://tomatch.me/' . $userId . '/' . $personality;