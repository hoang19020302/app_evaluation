<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Mail\EvaluationInvitation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;


class SendEmailController extends Controller
{
    //POST send-email
    public function sendEmailWithLink(Request $request)
    {
        // Tạo thời gian hết hạn 30 phút
        $expiration = now()->addMinutes(30);

        // Tách chuỗi email thành mảng các địa chỉ email
        $emails = explode(';', $request->input('emails'));

        // Lặp qua mỗi địa chỉ email và gán một token riêng cho mỗi email
        foreach ($emails as $email) {
            // Tạo token với expiration
            $token = Crypt::encryptString($expiration . '_' . $email);
            
            // Lưu token và thông tin cần thiết vào cache
            Cache::put($token, [
                //'userId' => $request->input('userId'),
                'classify' => $request->input('classify'),// Phân loại bài đánh giá tuỳ thuộc giá trị gửi bên fe
                'expiration' => $expiration,
            ], $expiration);

            // Tạo URL chứa token, thay thế tham số đầu tiên băng link phù hợp VD: 'http://localhost:5500/check?token=' . $token
            $evaluationLink = route('check.token', ['token' => $token]); 

            // Gửi email với liên kết
            $emailContent = $this->getEmailContent($request->input('classify'));
            $this->sendEmail($email, $emailContent, $evaluationLink, $expiration);
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

    private function sendEmail($email, $emailContent, $evaluationLink, $expiration) {
        try {
            Mail::to($email)->send(new EvaluationInvitation($emailContent, $evaluationLink, $expiration));
        } catch(Exception $e) {
            return response()->json(['error' => 'Failed to send email'], 500);
        }
    }
}