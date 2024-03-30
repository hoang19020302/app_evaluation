<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Enums\ServiceStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendEmailJob3;
use App\Mail\JoinRegisterApp;

class PermissionAdminController extends Controller
{
    //GET /api/permission/admin?permissions=1
    public function permissionAdmin(Request $request) {
        $permissions = $request->query('permissions');
        if ($permissions != 1) {
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
    
            $emailsOpened = DB::table('email_opens')->pluck('email');
            $totalLetterOpened = $emailsOpened->count();
            $emailsOpened = $emailsOpened->unique();
            $emailsNoOpened = $invitedEmailsArray->diff($emailsOpened);
            $emailsNoOpened = $emailsNoOpened->values();

            // Lấy chi tiết thông tin email mở thư
            $emailOpenInfo = [];
            foreach ($emailsOpened as $email) {
                $totalDisc = DB::table('email_opens')->where('email', $email)->where('type', '1')->count();
                $totalBeck = DB::table('email_opens')->where('email', $email)->where('type', '2')->count();
                $emailOpenInfo[$email] = "BECK: $totalBeck DISC: $totalDisc";
            }

            // Lấy số lượng bài đáng giá DISC và BECK mỗi email đã làm
            $emailInfo = [];
            $totalBeckCount = 0;
            $totalDiscCount = 0;
            $emailResults = DB::table('personalresult')
                        ->select('personalresult.EmailInformation', 
                                DB::raw('SUM(CASE WHEN questionbank.QuestionBankType = 2 THEN 1 ELSE 0 END) AS beck_test_count'), 
                                DB::raw('SUM(CASE WHEN questionbank.QuestionBankType = 1 THEN 1 ELSE 0 END) AS disc_test_count'))
                        ->join('questionbank', 'personalresult.QuestionBankID', '=', 'questionbank.QuestionBankID')
                        ->groupBy('personalresult.EmailInformation')
                        ->get();
            foreach ($emailResults as $result) {
                $email = $result->EmailInformation;
                $beckTestCount = $result->beck_test_count;
                $discTestCount = $result->disc_test_count;
                
                $totalBeckCount += $beckTestCount;
                $totalDiscCount += $discTestCount;
                
                $emailInfo[$email] = "BECK: $beckTestCount DISC: $discTestCount";
            }
            // Kết quả tổng số lượng bài test BECK và DISC
            $totalTestCount = [
                'total_beck_count' => $totalBeckCount,
                'total_disc_count' => $totalDiscCount
            ];

            // Đếm số tk ko đc đăng ký
            $emailJoin = DB::table('personalresult')->pluck('EmailInformation');
            $emailJoin = $emailJoin->unique();
            $emailRegister = DB::table('user')->pluck('UserName');
            $notRegisteredEmails = $emailJoin->diff($emailRegister);
            $notRegisteredEmails = $notRegisteredEmails->values();

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
                        'total_letter_opened' => $totalLetterOpened,//Số email đc gửi
                        'total_letter_sent' => $totalLetterSent,//Số email đã mở
                        'total_group' => $totalGroup, //Số nhóm đc tạo
                        'list_email_opened' => $emailOpenInfo,  //danh sách chi tiết các email mở thư
                        'list_email_no_opened' => $emailsNoOpened,//danh sách email ko mở thư
                        'list_email_not_registered' => $notRegisteredEmails,//danh sách các email làm bài test nhưng chưa đăng ký
                        'total_info_group' => $groupInfo,// thông tin chi tiết của người tạo nhóm
                        'email_info_test' => $emailInfo,//thông tin về số lg bài test từng email
                        'total_test_count' => $totalTestCount,//Tổng số lg bài test theo loại
                        'total_email_count' => $totalEmailCount//Số lg email tham gia theo từng loại bài test
                    ]
                ]);
            } else {
                return response()->json(['status' => ServiceStatus::Fail, 'message' => 'Không có dữ liệu để trả về']);
            }
        }
    }

    //POST /email-no-register
    public function emailNoRegister(Request $request) {
        $email = $request->input('email');
        $link = 'http://127.0.0.1:3000/register';
        $title = 'Mời tham gia đăng ký ứng dụng';
        Mail::to($email)->send(new JoinRegisterApp($title, $link));
        //SendEmailJob3::dispatch($email, $title, $link)->onQueue('email_3');
        return response()->json(['status' => ServiceStatus::Success,'message' => 'Gửi email thành công.']);
    }

    //GET /info-test/{email}
    public function emailInfoTest(Request $request, $email) {
        $results = DB::table('personalresult')
                    ->select('CreatedDate', 'QuestionBankID', 'GroupInformationID')
                    ->where('EmailInformation', $email)
                    ->groupBy('CreatedDate', 'QuestionBankID', 'GroupInformationID')
                    ->orderBy('CreatedDate', 'desc')
                    ->get();
        $emailInfo = [];
        $type = '';
        foreach ($results as $result) {
            $questionBankID = $result->QuestionBankID;
            $createdDate = $result->CreatedDate;
            $groupID = $result->GroupInformationID;
            $questionBankType = DB::table('questionbank')->where('QuestionBankID', $questionBankID)->value('QuestionBankType');
            $type = $questionBankType == 2 ? 'BECK' : 'DISC';
            $emailInfo[$createdDate.' - '.$groupID][$questionBankID] = $type;
        }
        return response()->json(['status' => ServiceStatus::Success, 'data' => $emailInfo]);
    }
}
