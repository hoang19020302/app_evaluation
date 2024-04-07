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
use stdClass;

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
                $individualEmails = array_filter(array_unique($individualEmails));
                foreach ($individualEmails as $email) {  
                    $invitedEmailsArray[] = trim($email);    
                }
            }
            $totalLetterSent =count($invitedEmailsArray);
            $invitedEmailsArray = collect(array_unique($invitedEmailsArray));
    
            $emailsOpened = DB::table('emailopens')
                            ->select('SentTime_ms', 'Email')
                            ->groupBy('SentTime_ms', 'Email')
                            ->get();               
            $totalLetterOpened = $emailsOpened->count();
            $emailsOpened = $emailsOpened->unique('Email')->pluck('Email');
            $emailsNoOpened = $invitedEmailsArray->diff($emailsOpened);
            $emailsNoOpened = $emailsNoOpened->values();

            // Lấy chi tiết thông tin email mở bài test
            $emailOpenInfo = [];
            foreach ($emailsOpened as $email) {
                $totalDisc = DB::table('emailopens')->where('Email', $email)->where('Type', '1')->count();
                $totalBeck = DB::table('emailopens')->where('Email', $email)->where('Type', '2')->count();
                $emailOpenInfo[$email] = ['BECK' => $totalBeck, 'DISC' => $totalDisc];
            }

            // Lấy số lượng bài đáng giá DISC và BECK mỗi email đã làm
            $emailInfo = [];
            $totalBeckCount = 0;
            $totalDiscCount = 0;
            $emailResults = DB::table('personalresult')
                        ->leftJoin('user', 'personalresult.UserID', '=', 'user.UserID')
                        ->join('questionbank', 'personalresult.QuestionBankID', '=', 'questionbank.QuestionBankID')
                        ->select(
                                 DB::raw('COALESCE(CAST(user.UserName AS CHAR), JSON_UNQUOTE(JSON_EXTRACT(personalresult.EmailInformation, "$.Email"))) as Email'), 
                                 DB::raw('GROUP_CONCAT(DISTINCT JSON_UNQUOTE(JSON_EXTRACT(personalresult.EmailInformation, "$.Name"))) as Names'),
                                 DB::raw('SUM(CASE WHEN questionbank.QuestionBankType = 2 THEN 1 ELSE 0 END) AS beck_test_count'), 
                                 DB::raw('SUM(CASE WHEN questionbank.QuestionBankType = 1 THEN 1 ELSE 0 END) AS disc_test_count')
                                )
                        ->groupBy('Email')
                        ->get();
            foreach ($emailResults as $result) {
                $email = $result->Email ? $result->Email : 'null';
                $nameString = $result->Names ? $result->Names : 'null';
        
                $beckTestCount = $result->beck_test_count;
                $discTestCount = $result->disc_test_count;
                
                $totalBeckCount += $beckTestCount;
                $totalDiscCount += $discTestCount;
                
                $emailInfo[$email] = ['BECK' => intval($beckTestCount), 'DISC' => intval($discTestCount), 'ListName' => $nameString];
            }
          
            // Kết quả tổng số lượng bài test BECK và DISC
            $totalTestCount = [
                'total_beck_count' => $totalBeckCount,
                'total_disc_count' => $totalDiscCount
            ];

            // Đếm số tk ko đc đăng ký
            $emailJoin = DB::table('personalresult')
                            ->leftJoin('user', 'personalresult.UserID', '=', 'user.UserID')
                            ->select(DB::raw('COALESCE(CAST(user.UserName AS CHAR), JSON_UNQUOTE(JSON_EXTRACT(personalresult.EmailInformation, "$.Email"))) as Email'))
                            ->groupBy('Email')
                            ->get();
            $emailJoinTest = $emailJoin->pluck('Email');
            $emailRegister = DB::table('user')->pluck('UserName');
            $notRegisteredEmails = $emailJoinTest->diff($emailRegister);
            
            $notRegisteredEmails = count($notRegisteredEmails) > 0 ? $notRegisteredEmails->values() : 'null';

            // Số email tham gia từng bài test
            $usersByType = DB::table('emailopens')
                ->select('Type', DB::raw('COUNT(DISTINCT Email) as total'))
                ->groupBy('Type')
                ->get();

            $totalEmailDisc = 0;
            $totalEmailBeck = 0;

            foreach ($usersByType as $user) {
                if ($user->Type == 1) {
                    $totalEmailDisc = $user->total;
                } elseif ($user->Type == 2) {
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

            $data = new stdClass();
            $data->total_letter_opened = $totalLetterOpened;//Số thư đc gửi
            $data->total_letter_sent = $totalLetterSent;//Số thư đã mở
            $data->total_group = $totalGroup; //Số nhóm đc tạo
            $data->list_email_opened = $emailOpenInfo;  //danh sách chi tiết các email mở thư
            $data->list_email_no_opened = $emailsNoOpened;//danh sách email ko mở thư
            $data->list_email_not_registered = $notRegisteredEmails;//danh sách các email làm bài test nhưng chưa đăng ký
            $data->total_info_group = $groupInfo;// thông tin chi tiết của người tạo nhóm
            $data->email_info_test = $emailInfo;//thông tin về số lg bài test từng email
            $data->total_test_count = $totalTestCount;//Tổng số lg bài test theo loại
            $data->total_email_count = $totalEmailCount;//Số lg email tham gia theo từng loại bài test


            // Trả lại dữ liệu cho admin
            if ($totalLetterSent > 0) {
                return response()->json([
                    'status' => ServiceStatus::Success,
                    'data' => $data
                ]);
            } else {
                return response()->json(['status' => ServiceStatus::Fail, 'message' => 'Không có dữ liệu để trả về']);
            }
        }
    }

    //POST /email-no-register
    public function emailNoRegister(Request $request) {
        $email = $request->input('email');
        $permissions = $request->input('permissions');
        if ($permissions != 1) {
            return response()->json(['status' => ServiceStatus::Fail, 'message' => 'Bạn không có quyền truy cập']);
        }
        $link = 'http://127.0.0.1:3000/register';
        $title = 'Mời tham gia đăng ký ứng dụng';
        Mail::to($email)->send(new JoinRegisterApp($title, $link));
        //SendEmailJob3::dispatch($email, $title, $link)->onQueue('email_3');
        return response()->json(['status' => ServiceStatus::Success,'message' => 'Gửi email thành công.']);
    }

    //GET /info-test/{email}
    public function emailInfoTest(Request $request, $email) {
        $permissions = $request->query('permissions');
        if ($permissions != 1) {
            return response()->json(['status' => ServiceStatus::Fail, 'message' => 'Bạn không có quyền truy cập']);
        }
        $results = DB::table('personalresult')
                    ->leftJoin('user', 'personalresult.UserID', '=', 'user.UserID')
                    ->select('personalresult.CreatedDate', 'personalresult.QuestionBankID', 'personalresult.PersonalResultID', 'personalresult.GroupInformationID')
                    ->where(function($query) use ($email) {
                        $query->where('user.UserName', '=', $email);
                    })
                    ->orWhere('personalresult.EmailInformation', 'like', "%$email%")
                    ->groupBy('personalresult.CreatedDate', 'personalresult.QuestionBankID', 'personalresult.PersonalResultID', 'personalresult.GroupInformationID')
                    ->orderBy('personalresult.CreatedDate', 'desc')
                    ->get();
        $emailInfo = [];
        $type = '';
        foreach ($results as $result) {
            $questionBankID = $result->QuestionBankID;
            $createdDate = $result->CreatedDate;
            $personalResultID = $result->PersonalResultID;
            $groupInformationID = $result->GroupInformationID;
            $questionBankType = DB::table('questionbank')->where('QuestionBankID', $questionBankID)->value('QuestionBankType');
            $type = $questionBankType == 2 ? 'BECK' : 'DISC';
            $emailInfo[$createdDate . ' - ' . $groupInformationID][$personalResultID] = $type;
        }
        if (empty($emailInfo)) {
            return response()->json(['status' => ServiceStatus::Error, $email => null]);
        }
        return response()->json(['status' => ServiceStatus::Success, $email => $emailInfo]);
    }
    // GET /detail-info-test/{personalResultID}
    public function detailInfoTest(Request $request, $personalResultID) {
        $permissions = $request->query('permissions');
        if ($permissions != 1) {
            return response()->json(['status' => ServiceStatus::Fail, 'message' => 'Bạn không có quyền truy cập']);
        }
        $personalResult = DB::table('personalresult')
                            ->join('questionbank', 'personalresult.QuestionBankID', '=', 'questionbank.QuestionBankID')
                            ->select('personalresult.Result', 'questionbank.QuestionBankType')
                            ->where('personalresult.PersonalResultID', $personalResultID)
                            ->first(); 
        $resultTest = json_decode($personalResult->Result, true);  
        $typeTest = $personalResult->QuestionBankType == 2 ? 'BECK' : 'DISC';
        $countTrue = 0;
        $countFalse = 0;
       
        $counts = [];
        foreach ($resultTest['Result'] as $result) {
            $answer = $result['Answer'];
            $questionID = $result['QuestionID'];
            $questionType = DB::table('question')->where('QuestionID', $questionID)->value('QuestionType');
            if (!isset($counts[$questionType])) {
                $counts[$questionType] = ['total' => 0, 'correct' => 0, 'incorrect' => 0];
            }
            $counts[$questionType]['total']++;
            if ($answer == true) {
                $countTrue++;
                $counts[$questionType]['correct']++;
            } else {
                $countFalse++;
                $counts[$questionType]['incorrect']++;
            }
        }
        
        return response()->json([
            'status' => ServiceStatus::Success,
            $typeTest => [
                'count_true' => $countTrue,
                'count_false' => $countFalse,
                'counts' => $counts
            ]
        ]);
    }
}
