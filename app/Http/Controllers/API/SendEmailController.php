<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Exception;
use Carbon\Carbon;
use App\Mail\EvaluationInvitation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Enums\ServiceStatus;


class SendEmailController extends Controller
{
    //POST send-email
    public function sendEmailWithLink(Request $request)
    {
        // Tạo thời gian hết hạn 30 phút
        $expiration = now()->addMinutes(30);

        // Tách chuỗi email thành mảng các địa chỉ email
        $emails = preg_split("/[;\\\n]+/", $request->input('emails'));
       //$uniqueEmails = array_unique($emails); // Loại bỏ các email trùng lặp
        // Lặp qua mỗi địa chỉ email và gán một token riêng cho mỗi email
        foreach ($emails as $email) {
            // Tạo token với expiration
            $token = Crypt::encryptString($expiration . '_' . $email);

            // Tạo URL chứa token, thay thế tham số đầu tiên băng link phù hợp VD: 'http://localhost:5500/check?token=' . $token
            $evaluationLink = route('check.token', ['token' => $token, 'personality' => $request->input('personality')]); 

            // Gửi email với liên kết
            $emailContent = $this->getEmailContent($request->input('personality'));
            $this->sendEmail($email, $emailContent, $evaluationLink, $expiration);
        }
       
        return response()->json(['status' => ServiceStatus::Success, 'success' => 'Các email đã được gửi thư thành công!']);
    }

    private function getEmailContent($personality) {
        if ($personality === 'character') {
            return 'Tham gia vào bài đánh giá tính cách. ';
        } elseif ($personality === 'mentality') {
            return 'Tham gia lại bài đánh giá tâm lý. ';
        }
    }

    private function sendEmail($email, $emailContent, $evaluationLink, $expiration) {
        try {
            Mail::to($email)->send(new EvaluationInvitation($emailContent, $evaluationLink, $expiration));
        } catch(Exception $e) {
            return response()->json(['status'=>ServiceStatus::Error, 'error' => 'Failed to send email']);
        }
    }
}