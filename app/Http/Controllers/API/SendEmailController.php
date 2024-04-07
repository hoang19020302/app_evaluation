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
        // Tách chuỗi email thành mảng các địa chỉ email
        $emails = explode(';', $emailsString);
        // Kiem tra đinh dạng email
        $validEmails = [];
        foreach ($emails as $email) {
            $trimmedEmail = trim($email); // Loại bỏ các khoảng trắng ở đầu và cuối email
            if (filter_var($trimmedEmail, FILTER_VALIDATE_EMAIL)) {
                $validEmails[] = $trimmedEmail;
            }
        }
        $emailsArr = array_filter(array_unique($validEmails));
        $title = 'Tham gia các bài đánh giá trên tomatch.me';
        // Thêm groupInformation trong database và lấy groupInformationID
        $groupInformationID = $this->insertData($emailsString, $groupName, $types, $userID);
        if (empty($groupInformationID)) {
                return response()->json(['status' => ServiceStatus::Fail,'message' => 'Không thể gửi thư do thiếu dữ liệu']);
        }
        // Xử lí vòng lặp
            foreach ($emailsArr as $email) {
                $linkArray = [];
                $sentTime = Carbon::now()->toDateTimeString();
                $microSentTime = round(microtime(true) * 1000);
                foreach ($types as $questionBankType) {
                    $content = $this->getEmailContent($questionBankType);
                    // Tạo token với groupInformationID và email
                    $token = Crypt::encryptString($groupInformationID . '_' . $email . '_' . $questionBankType . '_' . $microSentTime);
                    //$link = 'http://127.0.0.1:3000/' . 'group-test/' . $groupInformationID . '/test' . '/' . $questionBankID;
                    // Tạo url theo dõi
                    $link = route('track.email.open', ['token' => $token]);
                    $linkArray[$link] = $content;
                }
                // Gửi email với liên kết
                $this->sendEmail($email, $title, $linkArray, $sentTime);
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

    private function insertData($emailsString, $groupName, $types, $userID) {
        $groupInformationID = '';
        $questionBankIDArray = []; 
        foreach ($types as $questionBankType) {
            $randomQuestionBankID = DB::table('questionbank')
                                        ->select('QuestionBankID')
                                        ->where('QuestionBankType', $questionBankType)
                                        ->inRandomOrder()
                                        ->first();
            $questionBankIDArray[] = $randomQuestionBankID->QuestionBankID;

        }
        DB::beginTransaction();
        if (count($questionBankIDArray) == count($types)) {
            $groupInformationID = Str::uuid();
            $questionBankIDString = implode(';', $questionBankIDArray);
            DB::table('groupinformation')->insert([
                'GroupInformationID' => $groupInformationID,
                'UserID' => $userID,
                'GroupName' => $groupName,
                'QuestionBankID' => $questionBankIDString,
                'InvitedEmails' => $emailsString,
                'CreatedDate' => Carbon::now(),
            ]);
            DB::commit();
        } else {
            DB::rollBack();
            return null;
        }
        return $groupInformationID;
    }

    private function sendEmail($email, $title, $linkArray, $sentTime) {
        try {
            // Thêm công việc gửi email vào hàng đợi Beanstalkd
            SendEmailJob1::dispatch($email, $title, $linkArray, $sentTime)->onQueue('emails_1');
        } catch (Exception $e) {
            return response()->json(['status' => ServiceStatus::Fail, 'message' => $e->getMessage()]);
        }
    }
}