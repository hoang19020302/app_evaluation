<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Enums\ServiceStatus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Jobs\SendEmailJob4;

class ResetPasswordController extends Controller
{
    //POST /api/email-auth
    public function emailAuth(Request $request) {
        $email = $request->input('email');
        $user = DB::table('user')->select('UserID', 'FullName')->where('UserName', $email)->first();
        if ($user) {
            $userID = $user->UserID;
            $cacheKey = 'reset_password_' . $userID;
            $verifyCode = random_int(100000, 999999);
            Cache::put($cacheKey, $verifyCode, Carbon::now()->addMinutes(5));
            $title = 'Nhận mã xác thực';
            SendEmailJob4::dispatch($email, $title, $user->FullName, $verifyCode)->onQueue('emails_4');
            return response()->json(['status' => ServiceStatus::Success, 'userID' => $userID, 'message' => 'Mã xác thực đã được tạo.']);
        } else {
            return response()->json(['status' => ServiceStatus::Error, 'message' => 'Email chưa đăng ký.']);
        }
    }

    // POST /api/verify-code
    public function verifyCode(Request $request) {
        $verifyCode = $request->input('code');
        $userID = $request->input('userID');
        $cacheKey = 'reset_password_' . $userID;
        $checkCode = Cache::get($cacheKey);
        if ($checkCode === intval($verifyCode)) {
            Cache::forget($cacheKey);
            return response()->json(['status' => ServiceStatus::Success, 'userID' => $userID, 'message' => 'Xac nhận thành công.']);
        } elseif (Cache::has($cacheKey) === false) {
            return response()->json(['status' => ServiceStatus::Fail, 'message' => 'Mã xác nhận đã hết hạn.']);
        } else {
            Cache::forget($cacheKey);
            return response()->json(['status' => ServiceStatus::Error, 'message' => 'Xác nhận thất bại.']);
        }
    }

    // POST /api/repeat-code
    public function repeatCode(Request $request) {
        $userID = $request->input('userID');
        $user = DB::table('user')->select('FullName')->where('UserID', $userID)->first();
        $cacheKey = 'forgot_password_' . $userID;
        $verifyCode = random_int(100000, 999999);
        Cache::put($cacheKey, $verifyCode, Carbon::now()->addMinutes(5));
        $sendTime = Carbon::now();
        $nameWithPixel = $user->FullName . '<img src="' . route('track.email.open', ['email' => $email, 'action' => 'reset', 'sendTime' => $sendTime]) . '" style="display:none">';
        Mail::to($email)->send(new VerificationCode($nameWithPixel, $verifyCode));
        return response()->json(['status' => ServiceStatus::Success, 'userID' => $userID, 'message' => 'Mã xác nhận đã được tạo lại.']);
    }

    // POST /api/not-google-email
    public function notGoogleEmail(Request $request) {
        $userID = $request->input('userID');
        $user = DB::table('user')->select('UserName')->where('UserID', $userID)->first();  
        $verifyCode = Cache::get('reset_password_' . $userID);
        if (!$verifyCode) {
            return response()->json(['status' => ServiceStatus::Fail, 'message' => 'Mã xác thực đã hết hạn. Bạn hãy tạo mã xác thực mới.']);
        }
        return response()->json(['status' => ServiceStatus::Success, 'verifyCode' => $verifyCode, 'message' => 'Gửi lại mã xác thực cho người dùng ']);
    }

    // POST /api/reset-password
    public function resetPassword(Request $request) {
        $userID = $request->input('userID');
        $newPassword = $request->input('newPassword');
        $repeatPassword = $request->input('repeatPassword');
        if ($newPassword != $repeatPassword) {
            return response()->json(['status' => ServiceStatus::Fail, 'message' => 'Xác nhận mật khẩu không khớp.']);
        }
        DB::table('user')->where('UserID', $userID)->update(['Password' => Hash::make($newPassword), 'ModifiedDate' => Carbon::now()]);
        return response()->json(['status' => ServiceStatus::Success, 'message' => 'Thay đổi mật khẩu thành cảnh công.']);
    }
}
