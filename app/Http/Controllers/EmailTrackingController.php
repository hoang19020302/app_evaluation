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
        $token = $request->query('token');
        $desiredQuestionBankID = null;
        // Giải mã token
        $decodedToken = Crypt::decryptString($token);
        [$groupInformationID, $email, $questionBankType, $sentTime] = explode('_', $decodedToken);
        // Lấy và xử lý du liệu
        $questionBankIDString = DB::table('groupinformation')->select('QuestionBankID')->where('GroupInformationID', $groupInformationID)->first();
        $questionBankIDArray = array_filter(explode(';', $questionBankIDString->QuestionBankID));
        foreach ($questionBankIDArray as $questionBankID) {
            $questionBankInfo = DB::table('questionbank')->select('QuestionBankType')->where('QuestionBankID', $questionBankID)->first();
            if ($questionBankInfo->QuestionBankType == $questionBankType) {
                $desiredQuestionBankID = $questionBankID; 
                break; 
            }
        }
        $status = 'not_exist';
        // Lưu thông tin về việc email đã được mở vào cơ sở dữ liệu
        $userEmail = DB::table('user')->where('UserName', $email)->first();
        if ($userEmail) {
            $status = 'exist';
        }
        // Lưu vào database
        DB::beginTransaction();
        try{
            DB::table('emailopens')->insert([
                'Email' => $email, 
                'Status' => $status,
                'Type' => $questionBankType, 
                'SentTime_ms' => $sentTime,
                'OpenTime' => Carbon::now(),
            ]);
        DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::to(route('error'))->with('message', 'Lỗi khi lưu dữ liệu: ' . $e->getMessage());
        }
        // Tạo link
        if ($desiredQuestionBankID) {
            $urlApp = 'http://localhost:3000/' . 'group-test/' . $groupInformationID . '/test' . '/' . $desiredQuestionBankID;
            return Redirect::to($urlApp);
        } else {
            return Redirect::to(route('error'))->with('message', 'Không tìm thấy bài test!');
        }
    }

    public function analytics(Request $request)
    {   
        // GET /api/analytics
        $permissions = $request->query('permissions');
        if ($permissions == 0) {
            return response()->json(['status'=>ServiceStatus::Success, 'data'=>null, 'message'=>'Bạn không có quyền truy cập']);
        } elseif ($permissions == 1) {
            // Lấy ra các email không mở thư , số thư đc gửi, số thư đã mở
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
            $totalLetterSent =count($invitedEmailsArray);
            $invitedEmailsArray = collect(array_unique($invitedEmailsArray));
            //$totalEmailSent = $invitedEmailsArray->count();
    
            $emailsOpened = DB::table('email_opens')->pluck('email');
            $totalLetterOpened = $emailsOpened->count();
            $emailsOpened = $emailsOpened->unique();
            //$totalEmailOpened = $emailsOpened->count();
            $emailsNoOpened = $invitedEmailsArray->diff($emailsOpened);
            $emailsNoOpened = $emailsNoOpened->values();
            // Lấy chi tiết thông tin email mở thư
            $emailOpenInfo = [];
            foreach ($emailsOpened as $email) {
                $totalDisc = DB::table('email_opens')->where('email', $email)->where('type', '1')->count();
                $totalBeck = DB::table('email_opens')->where('email', $email)->where('type', '2')->count();
                $emailOpenInfo[$email] = "BECK: $totalBeck DISC: $totalDisc";
            }
    
            // Đếm số tk ko đc đăng ký
            // $notRegisteredEmails = DB::table('email_opens')
            //                         ->select('email')
            //                         ->where('status', 'not_exist')
            //                         ->pluck('email');
            $emailJoin = DB::table('personalresult')->pluck('EmailInformation');
            $emailJoin = $emailJoin->unique();
            $emailRegister = DB::table('user')->pluck('UserName');
            $notRegisteredEmails = $emailJoin->diff($emailRegister);
            $notRegisteredEmails = $notRegisteredEmails->values();

            // Danh sách những email tham gia bài test
            $totalBeckCount = 0;
            $totalDiscCount = 0;

            $emailResults = DB::table('personalresult')
                ->select('personalresult.EmailInformation', 
                        DB::raw('SUM(CASE WHEN questionbank.QuestionBankType = 2 THEN 1 ELSE 0 END) AS beck_count'), 
                        DB::raw('SUM(CASE WHEN questionbank.QuestionBankType = 1 THEN 1 ELSE 0 END) AS disc_count'))
                ->join('questionbank', 'personalresult.QuestionBankID', '=', 'questionbank.QuestionBankID')
                ->groupBy('personalresult.EmailInformation')
                ->get();

            $emailInfo = [];

            foreach ($emailResults as $result) {
                $email = $result->EmailInformation;
                $beckCount = $result->beck_count;
                $discCount = $result->disc_count;

                $emailInfo[$email] = "BECK: $beckCount DISC: $discCount";

                $totalBeckCount += $result->beck_count;
                $totalDiscCount += $result->disc_count;
            }

            // Kết quả tổng số lượng bài test BECK và DISC
            $totalTestCount = [
                'total_beck_count' => $totalBeckCount,
                'total_disc_count' => $totalDiscCount
            ];
        
            // Số email tham gia từng bài test
            $usersByType = DB::table('email_opens')
                ->select('type', DB::raw('COUNT(DISTINCT email) as total'))
                ->groupBy('type')
                ->get();

            $totalEmailDisc = 0;
            $totalEmailBeck = 0;

            foreach ($usersByType as $user) {
                if ($user->type == 1) {
                    $totalEmailDisc = $user->total;
                } elseif ($user->type == 2) {
                    $totalEmailBeck = $user->total;
                }
            }
            $totalEmailCount = [
                'total_email_beck' => $totalEmailBeck,
                'total_email_disc' => $totalEmailDisc
            ];

            // Số nhóm đã tạo của mỗi userID
            $groupInfo = [];
            $usersCreateGroup = DB::table('groupinformation')
                ->select('UserID', DB::raw('COUNT(DISTINCT GroupInformationID) as total'))
                ->groupBy('UserID')
                ->get();
            foreach ($usersCreateGroup as $user) {
                $email = DB::table('user')->where('UserID', $user->UserID)->value('UserName');
                $groupInfo[$email] = $user->total;
            }
            $totalGroup = (DB::table('groupinformation')->pluck('GroupInformationID'))->count();

            // Trả lại dữ liệu cho admin
            if ($totalLetterSent > 0) {
                return response()->json([
                    'status' => ServiceStatus::Success,
                    'data' => [
                        'total_letter_opened' => $totalLetterOpened,
                        'total_letter_sent' => $totalLetterSent,
                        'total_group' => $totalGroup,
                        'list_email_opened' => $emailOpenInfo,
                        'list_email_no_opened' => $emailsNoOpened,
                        'list_email_not_registered' => $notRegisteredEmails,
                        'total_info_group' => $groupInfo,
                        'email_info_test' => $emailInfo,
                        'total_test_count' => $totalTestCount,
                        'total_email_count' => $totalEmailCount
                    ]
                ]);
            } else {
                return response()->json(['status' => ServiceStatus::Fail, 'message' => 'Không có dữ liệu để trả về']);
            }
        }
    }
}
