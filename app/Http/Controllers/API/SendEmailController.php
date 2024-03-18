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
use Illuminate\Http\Exceptions\HttpResponseException;


class SendEmailController extends Controller
{
    //POST send-email
    public function sendEmailWithLink(Request $request)
    {
        //Logic lấy groupInformationID
        $invitedEmailsFormatted = preg_replace("/[\r\n\s;]+/", ';', $request->input('invitedEmails'));
        $groupName = $request->input('groupName');
        $groupInformations = DB::table('groupinformation')
                                ->select('GroupInformationID', 'CreatedDate')
                                ->where('GroupName', $groupName)
                                ->where('InvitedEmails', $invitedEmailsFormatted)
                                ->get();
        $count = $groupInformations->count();
        // Khởi tạo giá trị khoảng thời gian nhỏ nhất
        $minDifference = PHP_INT_MAX;
        $selectedGroupInformationID = null;
        if ($count == 1) {
            $selectedGroupInformationID = $groupInformations[0]->GroupInformationID;
        } elseif ($count > 1) {
            foreach ($groupInformations as $groupInformation) {
            // Tính khoảng thời gian từ createdDate đến hiện tại
                $createdDate = Carbon::parse($groupInformation->CreatedDate);
                $difference = Carbon::now()->diffInSeconds($createdDate);
    
                // So sánh với khoảng thời gian nhỏ nhất hiện tại
                if ($difference < $minDifference) {
                    $minDifference = $difference;
                    $selectedGroupInformationID = $groupInformation->GroupInformationID;
                }
            }
        }

        //Lấy ra QuestionBankID
        $questionBankID = DB::table('groupinformation')
                            ->where('GroupInformationID', $selectedGroupInformationID)
                            ->value('QuestionBankID');

        // Tạo thời gian hết hạn 30 phút
        $expiration = Carbon::now()->addMinutes(30);
        // Tách chuỗi email thành mảng các địa chỉ email
        $invitedEmails = explode(';', $invitedEmailsFormatted);
        $invitedEmails = array_filter(array_unique($invitedEmails));
        // Lặp qua mỗi địa chỉ email và gán một token riêng cho mỗi email
        $brokenLink = route('error');
        foreach ($invitedEmails as $email) {
            //Lấy fullname tuong ung voi email
            $name = DB::table('user')->where('UserName', $email)->value('FullName');
            // Tạo  URL dựa trên questionBankID và groupInformationID
            $evaluationLink = 'https://tomatch.me/' . $selectedGroupInformationID . '/' . $questionBankID;

            // Gửi email với liên kết
            $emailContent = $this->getEmailContent($request->input('personality'));
            $this->sendEmail($email, $emailContent, $evaluationLink, $expiration, $groupName, $name, $brokenLink);
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

    private function sendEmail($email, $emailContent, $evaluationLink, $expiration, $groupName, $name, $brokenLink) {
        $timeout = 11;
        $emailSent = false; 
    
        try {
            // Thực hiện gửi email và retry trong trường hợp timeout
            retry(3, function () use ($email, $emailContent, $evaluationLink, $expiration, $groupName, $name, $brokenLink, &$emailSent) {
                if (!$emailSent) {
                    Mail::to($email)->send(new EvaluationInvitation($emailContent, $evaluationLink, $expiration, $groupName, $name, $brokenLink));
                    $emailSent = true; // Đánh dấu email đã được gửi thành công
                }
            }, $timeout * 1000);
        } catch (Exception $e) {
            if (!$emailSent) {
                throw new HttpResponseException(response()->json(['status'=>ServiceStatus::Error, 'error' => 'Failed to send email. Server timed out.']));
            }
        }
    }
}
