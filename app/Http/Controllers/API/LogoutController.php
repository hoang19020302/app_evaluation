<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class LogoutController extends Controller
{
    //POST api/logout
    public function logoutUser(Request $request) {
        $personalResultID = Str::uuid();

        if (!$request->result || !$request->questionBankID) {
            return response()->json(['status'=>ServiceStatus::Fail,'message'=> 'Thiếu dữ liệu cần thiết']);
        }
            DB::beginTransaction();
            DB::table('personalresult')
            ->insert(['PersonalResultID'=>$personalResultID,
                    'UserID'=>$request->userID,
                    'Result'=>$request->result,
                    'GroupInformationID'=>$request->groupInformationID,
                    'QuestionBankID'=> $request->questionBankID,
                    'EmailInformation'=>$request->emailInformation,
                    'CreatedDate'=>Carbon::now()]);
            DB::commit();
        
        return response()->json(['success' => 'User logged out successfully'], 200);
    }
}
