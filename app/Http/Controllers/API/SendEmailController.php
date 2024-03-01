<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Mail\EvaluationInvitation;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;

class SendEmailController extends Controller
{
    //POST group-email
    public function sendEvaluationInvitations(Request $request) {
       // Tạo token có thời gian hết hạn 30 phút
    $expiration = now()->addMinutes(30);

    // Tách chuỗi email thành mảng các địa chỉ email
    $emails = explode(';', $request->input('emails'));

    // Lặp qua mỗi địa chỉ email và gán một token riêng cho mỗi email
    foreach ($emails as $email) {
        $token = Crypt::encryptString($expiration);
        
        // Lưu token và thông tin cần thiết vào cache
        Cache::put($token, [
            'classify' => $request->input('classify'),
            'email' => $email,
            'expiration' => $expiration,
        ], $expiration);

        // Tạo URL chứa token
        $evaluationLink = route($request->input('classify'), ['token' => $token]);

        // Gửi email với liên kết
        $emailContent = $this->getEmailContent($request->input('classify'));
        $brokenLink = route('error');
        $this->sendEmail($email, $emailContent, $evaluationLink, $expiration, $brokenLink);
    }

    return response()->json(['success' => 'Emails sent successfully'], 200);
    }

    private function getEmailContent($classify) {
        if ($classify === 'character') {
            return 'Tham gia vào bài đánh giá tính cách. ';
        } elseif ($classify === 'spirit') {
            return 'Tham gia lại bài đánh giá tinh thần. ';
        }
    }
    
    private function sendEmail($email, $emailContent, $evaluationLink, $expiration, $brokenLink) {
        Mail::to($email)->send(new EvaluationInvitation($emailContent, $evaluationLink, $expiration, $brokenLink));
    }
}
