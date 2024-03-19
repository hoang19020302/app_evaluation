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
use Swift_TransportException;


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

        // Tạo thời gian hết hạn 30 phút
        $expiration = Carbon::now()->addMinutes(30);
        // Tách chuỗi email thành mảng các địa chỉ email
        $invitedEmails = explode(';', $invitedEmailsFormatted);
        $invitedEmails = array_filter(array_unique($invitedEmails));
        // Tạo token với expiration và groupInformationID
        //$token = Crypt::encryptString($expiration . '_' . $selectedGroupInformationID);
        $questionBankID = DB::table('groupinformation')
                            ->where('GroupInformationID', $selectedGroupInformationID)
                            ->value('QuestionBankID');

        // Lặp qua mỗi địa chỉ email và gán một token riêng cho mỗi email
        foreach ($invitedEmails as $email) {
            //Lấy fullname tuong ung voi email
            $name = DB::table('user')->where('UserName', $email)->value('FullName');
            // Tạo  URL chứa token để kiểm tra thời gian sống của  link
            $evaluationLink = 'http://localhost:3000/' . 'group-test/' . $selectedGroupInformationID . '/test' . '/' . $questionBankID;
            // Gửi email với liên kết
            $emailContent = $this->getEmailContent($request->input('personality'));
            $this->sendEmail($email, $emailContent, $evaluationLink, $expiration, $name);
        }
        
        return response()->json(['status' => ServiceStatus::Success, 'success' => 'Emails sent successfully!']);
    }

    private function getEmailContent($personality) {
        if ($personality === 'character') {
            return 'Tham gia vào bài test tính cách. ';
        } elseif ($personality === 'mentality') {
            return 'Tham gia lại bài test tâm lý. ';
        }
    }

    private function sendEmail($email, $emailContent, $evaluationLink, $expiration, $name) {
        $invalidEmails = [];
        try {
            // Thực hiện gửi email 
            Mail::to($email)->send(new EvaluationInvitation($emailContent, $evaluationLink, $expiration, $name));
        } catch (Swift_TransportException $e) {
            $invalidEmails[] = ['email' => $email, 'message' => $e->getMessage()];
        } catch (Exception $e) {
            return response()->json(['status' => ServiceStatus::Error, 'error' => $e->getMessage()]);
        }
        // Trả về danh sách các địa chỉ email không hợp lệ
        return response()->json(['status' => ServiceStatus::Fail, 'invalid_emails' => $invalidEmails]);
    }
}
