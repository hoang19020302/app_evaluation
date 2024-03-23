<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmailOpen;
use Illuminate\Support\Facades\DB;
use App\Enums\ServiceStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;


class EmailTrackingController extends Controller
{
    //GET email/open
    public function trackEmailOpen(Request $request)
    {   
        // GET /email/open
        $token = $request->query('token');
        // Giải mã token
        $decodedToken = Crypt::decryptString($token);
        [$groupInformationID, $email] = explode('_', $decodedToken);
        $questionBankID = DB::table('groupinformation')->where('GroupInformationID', $groupInformationID)->value('QuestionBankID');
        $questionBankType = DB::table('questionbank')->where('QuestionBankID', $questionBankID)->value('QuestionBankType');
        $status = 'not_exist';
        // Lưu thông tin về việc email đã được mở vào cơ sở dữ liệu
        $userEmail = DB::table('user')->where('UserName', $email)->first();
        if ($userEmail) {
            $status = 'exist';
        }
        DB::table('email_opens')->insert([
            'email' => $email, 
            'status' => $status, 
            'opened_at' => Carbon::now(),
            'type' => $questionBankType, 
        ]);
        // Tạo hình ảnh pixel
        $urlApp = 'http://localhost:3000/' . 'group-test/' . $groupInformationID . '/test' . '/' . $questionBankID;

        return Redirect::to($urlApp);
    }

    public function analytics()
    {   
        // GET /api/analytics

        // Lấy tất cả các giá trị InvitedEmails
        $invitedEmailsFromDB = DB::table('groupinformation')->pluck('InvitedEmails');
        $invitedEmailsArray = [];
        foreach ($invitedEmailsFromDB as $emails) {
            $individualEmails = explode(';', $emails);
            foreach ($individualEmails as $email) {
                if (!empty($email)) {
                    $invitedEmailsArray[] = trim($email);
                }
            }
        }
        $invitedEmailsArray = collect(array_unique($invitedEmailsArray));
        $totalEmailsSent = $invitedEmailsArray->count();

        $emailsOpened = DB::table('email_opens')->pluck('email');
        $emailsOpened = $emailsOpened->unique();

        // Đếm số tk ko đc đăng ký
        $notRegisteredEmails = DB::table('email_opens')
                                ->select('email')
                                ->where('status', 'not_exist')
                                ->pluck('email');
        $notRegisteredEmails = $notRegisteredEmails->unique();

        // Kiểu bài test
        $characterUser = DB::table('email_opens')->select('email')->where('type', 1)->get();
        $totalCharacterUser = ($characterUser->unique())->count();
        $mentalityUser = DB::table('email_opens')->select('email')->where('type', 2)->get();
        $totalMentalityUser = ($mentalityUser->unique())->count();

        // Phân tích dữ liệu
        $totalEmailsOpened = $emailsOpened->count();
        $emailsNoOpened = $invitedEmailsArray->diff($emailsOpened);
        $emailsNoOpened = $emailsNoOpened->values();

        if ($totalEmailsOpened >= 50) {
            return response()->json([
                'status' => ServiceStatus::Success,
                'message' => 'Ứng dụng được nhiều người quan tâm.',
                'data' => [
                    'total_emails_opened' => $totalEmailsOpened,
                    'total_emails_sent' => $totalEmailsSent,
                    'count_email_character_user' => $totalCharacterUser,
                    'count_email_mentality_user' => $totalMentalityUser,
                    'list_email_no_opened' => $emailsNoOpened,
                    'list_email_not_registered' => $notRegisteredEmails
                ]
            ]);
        } elseif ($totalEmailsOpened < 50) {
            return response()->json(['status' => ServiceStatus::Fail, 'message' => 'Không đủ dữ liệu để phân tích.']);
        }
    }
}
