<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;

class CheckTokenController extends Controller
{
    // GET check-token?token=$token&groupInformationID=$groupInformationID&personality=$personality
    public function checkToken(Request $request) {
        // Lấy token va personality từ request
        $token = $request->query('token');
        $personality = $request->query('personality');

        // Giải mã token
        $decodedToken = Crypt::decryptString($token);
            // Tách thời gian hết hạn và email từ token
        [$expiration, $groupInformationID] = explode('_', $decodedToken);
        
       //Lấy ra QuestionBankID
        $questionBankID = DB::table('groupinformation')
                            ->where('GroupInformationID', $groupInformationID)
                            ->value('QuestionBankID');
        if (empty($questionBankID)) {
            return Redirect::to(route('error'))->with('message', 'Không tìm thấy bài test!');
        } 
        // Kiểm tra xem thời gian hết hạn của token
        if (Carbon::now()->lt($expiration)) {
            // Tạo URL dựa trên personality và email để chuyển hướng
            $tomatchUrl = 'http://localhost:3000/' . 'group-test/' . $groupInformationID . '/test' . '/' . $questionBankID;
            return Redirect::to($tomatchUrl);
        } else {
            // Token không hợp lệ hoặc đã hết hạn, chuyển hướng đến trang thông báo lỗi
            return Redirect::to(route('error'))->with('message', 'Không tìm thấy trang do link của bạn đã hết hạn.');  
        } 
    }
}
