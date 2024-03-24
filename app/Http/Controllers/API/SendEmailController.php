<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Exception;
use Carbon\Carbon;
use App\Jobs\SendEmailJob1;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Enums\ServiceStatus;
use Illuminate\Support\Str;

class SendEmailController extends Controller
{
    //POST /api/send-email
    public function sendEmailWithLink(Request $request)
    {
        //Format đầu vào
        $emailsString = preg_replace("/[\r\n\s;]+/", ';', $request->input('invited_emails'));
        $groupName = $request->input('group_name');
        $types = $request->input('type');
        $userID = $request->input('user_ID');
        if (empty($types) || empty($emailsString)) {
            return response()->json(['status' => ServiceStatus::Fail,'message' => 'Không thể gửi thư do thiếu dữ liệu']);
        }
        $message = '';
        // Tách chuỗi email thành mảng các địa chỉ email
        $emails = explode(';', $emailsString);
        $emailsArr = array_filter(array_unique($emails));

        // Xử lí vòng lặp
        foreach ($types as $questionBankType) {
            $expirationTime = Carbon::now()->addMinutes(30);
            $groupInformationID = $this->insertData($emailsString, $groupName, $questionBankType, $userID);
            $title = $this->getEmailTitle($questionBankType);
            $emailContent = $this->getEmailContent($questionBankType);
            foreach ($emailsArr as $email) {
                // Tạo token với groupInformationID và email
                $token = Crypt::encryptString($groupInformationID . '_' . $email);
                //$evaluationLink = 'http://127.0.0.1:3000/' . 'group-test/' . $groupInformationID . '/test' . '/' . $questionBankID;
                // Tạo url theo dõi
                $evaluationLink = route('track.email.open', ['token' => $token]);
                // Gửi email với liên kết
                $this->sendEmail($email, $title, $emailContent, $evaluationLink, $expirationTime);
            }
        }
        return response()->json(['status' => ServiceStatus::Success, 'message' => 'Gửi thư thành công cho các email']);  
    }

    private function getEmailContent($questionBankType) {
        if ($questionBankType == 1) {
            return 'Bài trắc nghiệm tính cách';
        } elseif ($questionBankType == 2) {
            return 'Bài trắc nghiệm tinh thần';
        }
    }

    private function getEmailTitle($questionBankType) {
        if ($questionBankType == 1) {
            return 'Tham gia bài trắc nghiệm tính cách - DISC';
        } elseif ($questionBankType == 2) {
            return 'Tham gia bài trắc nghiệm tinh thần - BECK';
        }
    }

    private function insertData($emailsString, $groupName, $questionBankType, $userID) {
        $groupInformationID = '';
        $randomQuestionBankID = DB::table('questionbank')
                                    ->where('QuestionBankType', $questionBankType)
                                    ->inRandomOrder()
                                    ->first();
        if ($randomQuestionBankID) {
            $groupInformationID = Str::uuid();
            $questionBankID = $randomQuestionBankID->QuestionBankID;
            DB::table('groupinformation')->insert([
                'GroupInformationID' => $groupInformationID,
                'UserID' => $userID,
                'GroupName' => $groupName,
                'QuestionBankID' => $questionBankID,
                'InvitedEmails' => $emailsString,
                'CreatedDate' => Carbon::now(),
            ]);
        }
        return $groupInformationID;
    }

    private function sendEmail($email, $title, $emailContent, $evaluationLink, $expirationTime) {
        try {
            // Thêm công việc gửi email vào hàng đợi Beanstalkd
            SendEmailJob1::dispatch($email, $title, $emailContent, $evaluationLink, $expirationTime)->onQueue('emails_1');
        } catch (Exception $e) {
            return response()->json(['status' => ServiceStatus::Fail, 'message' => $e->getMessage()]);
        }
    }
}