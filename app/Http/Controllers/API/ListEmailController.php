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
use Illuminate\Support\Str;
use App\Enums\ServiceStatus;


class ListEmailController extends Controller
{
    //GET /list-ema
    public function getEmails(Request $request) {
        $userId = DB::table('user')->where('UserName', $request->input('email'))->value('UserID');
        $uuid = Str::uuid();
            DB::table('groupinformation')
            ->insert(['GroupInformationID'=>$uuid,
                    'UserID'=>$userId,
                    'QuestionBankID'=>$request->questionBankID,
                    'GroupName'=> $request->groupName,
                    'InvitedEmails'=>$request->invitedEmails,
                    'CreatedDate'=>Carbon::now()]);

            $groupInformation = DB::table('groupinformation')
            ->select('GroupInformationID',
                    'UserID',
                    'QuestionBankID',
                    'GroupName',
                    'InvitedEmails')
            ->where('GroupInformationID', $uuid)
            ->first();

            return response()->json(['status'=>ServiceStatus::Success, 'data'=>$groupInformation]);
    }

}
