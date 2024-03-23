<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Exception;
use Carbon\Carbon;
//use App\Mail\EvaluationInvitation;
use App\Jobs\SendEmailJob;
use Illuminate\Support\Facades\Queue;
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
        //$invitedEmails = explode(';', $invitedEmailsFormatted);
        //$invitedEmails = array_filter(array_unique($invitedEmails));

        //$emailsString = implode(';', $invitedEmails);
        $groupName = $request->input('groupName');
        $questionBankID = $request->input('questionBankID');
        $userID = $request->input('userID');
        $checkUser = DB::table('user')->where('UserID', $userID)->first();
        $groupInformationID = Str::uuid();
        DB::table('groupinformation')->insert([
            'GroupInformationID' => $groupInformationID,
            'UserID' => $userID,
            'GroupName' => $groupName,
            'QuestionBankID' => $questionBankID,
            'InvitedEmails' => $emailsString,
            'CreatedDate' => Carbon::now(),
        ]);

        // Tạo thời gian hết hạn 30 phút
        $expirationTime = Carbon::now()->addMinutes(30);
        // Tách chuỗi email thành mảng các địa chỉ email
        $emails = explode(';', $emailsString);
        // Tạo token với expiration và groupInformationID
        //$token = Crypt::encryptString($expirationTime . '_' . $groupInformationID);
        $questionBankType = DB::table('questionbank')
                            ->where('QuestionBankID', $questionBankID)
                            ->value('QuestionBankType');

        // Lặp qua mỗi địa chỉ email và gán một token riêng cho mỗi email
        foreach ($emails as $email) {
            //Lấy fullname tuong ung voi email
            $token = Crypt::encryptString($groupInformationID . '_' . $email);
            // Tạo  URL chứa token để kiểm tra thời gian sống của  link
            $evaluationLink = 'http://127.0.0.1:3000/' . 'group-test/' . $groupInformationID . '/test' . '/' . $questionBankID;
            $evaluationLink = route('track.email.open', ['token' => $token]);
            $emailContent = $this->getEmailContent($questionBankType);
            
            $this->sendEmail($email, $emailContent, $evaluationLink, $expirationTime);
        }
        
        return response()->json(['status' => ServiceStatus::Success, 'success' => 'Emails sent successfully!']);
    }

    private function getEmailContent($questionBankType) {
        if ($questionBankType === 1) {
            return 'Tham gia vào bài đánh giá tính cách. ';
        } elseif ($questionBankType === 2) {
            return 'Tham gia lại bài đánh giá tâm lý. ';
        }
    }

    private function sendEmail($email, $emailContent, $evaluationLink, $expirationTime) {
        try {
            // Thực hiện gửi email 
            //Mail::to($email)->send(new EvaluationInvitation($emailContent, $evaluationLink, $expirationTime));
            SendEmailJob::dispatch($email, $emailContent, $evaluationLink, $expirationTime)->onQueue('emails');
        } catch (Exception $e) {
            return response()->json(['status' => ServiceStatus::Error, 'error' => $e->getMessage()]);
        }
    }
}