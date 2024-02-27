<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class CheckInfoController extends Controller
{
    //POST check-info
    public function checkInfo(Request $request) {
        // Lấy dữ liệu từ request
        $email = $request->input('email');
        $password = $request->input('password');
        $classify = $request->input('classify');

        // So sánh dữ liệu với dữ liệu trong cache
        if ($this->compareWithDataInCache($email, $password)) {
            // Trả về phản hồi thành công
            if($classify === 'spirit') {
                return redirect('http://172.23.176.1:5500/src/form/spirit.html');
                //return response()->json(['message' => true]);
            } elseif($classify === 'character') {
                return redirect('http://172.23.176.1:5500/src/form/character.html');
                //return response()->json(['message' => true]);
            }
            
        } else {
            // Trả về phản hồi không thành công
            return redirect('http://127.0.0.1:8000/failed-login');
            //return response()->json(['failed' => false]);
        }
    }

    private function compareWithDataInCache($email, $password)
    {
       // Lấy dữ liệu từ cache
    $cachedData = Cache::get('users_info', []);

    // Kiểm tra xem dữ liệu trong cache có tồn tại không
    if (!empty($cachedData)) {
        // Duyệt qua từng phần tử trong mảng
        foreach ($cachedData as $user) {
            // Kiểm tra xem có tồn tại trường 'email' và 'password' trong đối tượng người dùng không
            if (isset($user->email) && isset($user->password)) {
                // Kiểm tra xem 'email' và 'password' khớp với dữ liệu từ form không
                if ($user->email === $email && $user->password === $password) {
                    // Dữ liệu từ form khớp với dữ liệu trong cache
                    return true;
                }
            }
        }
    }
    
    // Dữ liệu từ form không khớp hoặc không tồn tại trong cache
    return false;
    }
}
