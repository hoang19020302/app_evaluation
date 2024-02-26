<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Mail\EvaluationInvitation;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;

class SendEmailController extends Controller
{
    //POST group-email
    public function sendEvaluationInvitations(Request $request) {
        $expiration = Carbon::now()->addDays(30)->diffInSeconds();

        // Lấy dữ liệu từ request
        $classify = $request->input('classify');
        $emailString = $request->input('emails');
        // tách chuỗi từ request
        //$emails = explode(';', $emailString);
        // Lưu dữ liệu vào cache
        $group = [
            'classify' => $classify,
            'emails' => $emailString,
        ];
        $groupInfo = Cache::get('group_info', []);
        $groupInfo[] = $group;
        Cache::put('group_info', $groupInfo, $expiration);

        // tách chuỗi từ request
        $emails = explode(';', $emailString);

        $emailContent = '';
        $evaluationLink = '';
        $brokenLink = 'http://127.0.0.1:8000/broken-link';
        //Kiem tra loại 
        if ($classify === 'character') {
            $emailContent = 'Tham gia vào bài đánh giá tính cách. ';
            $evaluationLink = 'http://127.0.0.1:8000/character';
        } elseif ($classify === 'spirit') {
            $emailContent = 'Tham gia lại bài đánh giá tinh thần. ';
            $evaluationLink = 'http://127.0.0.1:8000/spirit';
        }
        
        $expirationTime = Carbon::now()->addMinutes(30);
        // Tạo session cho đường link trong khoảng thời gian 30 phút
        $request->session()->put('evaluation_link', $evaluationLink);
        $request->session()->put('expiration_time', $expirationTime);
        $request->session()->save();

        // Gửi email
        // Kiểm tra nếu session tồn tại và thời gian hết hạn chưa tới
        if ($request->session()->has('evaluation_link') && $request->session()->get('expiration_time') > Carbon::now()) {
            $evaluationLink = $request->session()->get('evaluation_link');
            //Gửi link tới từng email trong mảng
            foreach ($emails as $email) {
                Mail::to($email)->send(new EvaluationInvitation($emailContent, $evaluationLink, $expirationTime, $brokenLink));
            }
            return response()->json(['success' => 'Email sent'], 200);
        }
        return response()->json(['error' => 'Link expired'], 400);
    }
}
