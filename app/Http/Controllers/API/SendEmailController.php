<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Exception;
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
        $token = Crypt::encryptString($expiration);

        // Tách chuỗi email thành mảng các địa chỉ email
        $emails = explode(';', $request->input('emails'));

        // Lưu token và thông tin cần thiết vào cache
        Cache::put($token, [
            'classify' => $request->input('classify'),
            'email' => $email,
            'expiration' => $expiration,
        ], $expiration);

        // Tạo URL chứa token
        $evaluationLink = route($request->input('classify'), ['token' => $token]);

        // Gửi email với liên kết
        $emailContent = $this->getEmailContent($request->input('classify'));  //self::
        $this->sendEmail($emails, $emailContent, $evaluationLink, $expiration); //self::

        return response()->json(['success' => 'Emails sent successfully'], 200);
    }

    private function getEmailContent($classify) {
        if ($classify === 'character') {
            return 'Tham gia vào bài đánh giá tính cách. ';
        } elseif ($classify === 'spirit') {
            return 'Tham gia lại bài đánh giá tinh thần. ';
        }
    }
    
    private function sendEmail($emails, $emailContent, $evaluationLink, $expiration) {
        try {
            // Gửi email
            foreach ($emails as $email) {
                Mail::to($email)->send(new EvaluationInvitation($emailContent, $evaluationLink, $expiration));
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to send email.'], 500);
        }
    }
}
