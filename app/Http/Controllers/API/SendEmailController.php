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
        $emailsString = preg_replace("/[\r\n\s;]+/", ';', $request->input('invitedEmails'));
        $groupName = $request->input('groupName');
        $types = array_filter($request->input('type'));
        $userID = $request->input('userID');
        if (empty($types) || empty($emailsString)) {
            return response()->json(['status' => ServiceStatus::Fail,'message' => 'Không thể gửi thư do thiếu dữ liệu']);
        }
        $message = '';
        // Tách chuỗi email thành mảng các địa chỉ email
        $emails = explode(';', $emailsString);
        $emailsArr = array_filter(array_unique($emails));
        $linkArray = [];
        $expirationTime = Carbon::now()->addMinutes(30);
        // Xử lí vòng lặp
            foreach ($emailsArr as $email) {
                foreach ($types as $questionBankType) {
                    $groupInformationID = $this->insertData($emailsString, $groupName, $questionBankType, $userID);
                    $title = 'Tham gia các bài đánh giá trên tomatch.me';
                    $content = $this->getEmailContent($questionBankType);
                    // Tạo token với groupInformationID và email
                    $token = Crypt::encryptString($groupInformationID . '_' . $email);
                    //$link = 'http://127.0.0.1:3000/' . 'group-test/' . $groupInformationID . '/test' . '/' . $questionBankID;
                    // Tạo url theo dõi
                    $link = route('track.email.open', ['token' => $token]);
                    $linkArray[$link] = $content;
                }
                // Gửi email với liên kết
                $this->sendEmail($email, $title, $linkArray, $expirationTime);
            }
        return response()->json(['status' => ServiceStatus::Success, 'message' => 'Gửi thư thành công cho các email']);  
    }

    private function getEmailContent($questionBankType) {
        $content = [];
        if ($questionBankType == 1) {
            $content['DISC'] = 'Bài trắc nghiệm tính cách';
        } elseif ($questionBankType == 2) {
            $content['BECK'] = 'Bài trắc nghiệm tinh thần';
        }
        return $content;
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

    private function sendEmail($email, $title, $linkArray, $expirationTime) {
        try {
            // Thêm công việc gửi email vào hàng đợi Beanstalkd
            SendEmailJob1::dispatch($email, $title, $linkArray, $expirationTime)->onQueue('emails_1');
        } catch (Exception $e) {
            return response()->json(['status' => ServiceStatus::Fail, 'message' => $e->getMessage()]);
        }
    }
}