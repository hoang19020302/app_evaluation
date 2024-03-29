<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use App\Enums\ServiceStatus;
use stdClass;

class UserController extends Controller
{
    /**
     * Đăng nhập tài khoản
     *
     * @param Request $request http request với bộ tham số tại body
     * userName: tài khoản người dùng
     * password: mật khẩu
     */
    public function login(Request $request) {
        try {
            $user = DB::table('user')
                    ->select('UserID', 'UserName', 'FullName', 'Password', 'DateOfBirth')
                    ->where('UserName', $request->userName)
                    ->first();

            if ($user && Hash::check($request->password, $user->Password)) {
                $expire_time = Carbon::now()->addMinutes(Config::get('session.lifetime'));
                session()->regenerate();
                session()->put('expire_time', $expire_time);
                $data = new stdClass();
                $data->UserID = $user->UserID;
                $data->UserName = $user->UserName;
                $data->FullName = $user->FullName;
                $data->DateOfBirth = $user->DateOfBirth;
                $data->SessionID = session()->getId();

                return response()->json(['status'=>ServiceStatus::Success, 'data'=>$data]);
            }

            return response()->json(['status'=>ServiceStatus::Fail, 'message'=>'Thông tin đăng nhập không chính xác.']);
        }
        catch (\Exception $e) {
            return response()->json(['status'=>ServiceStatus::Error, 'message'=>$e->getMessage()]);
        }
    }

    /**
     * Đăng xuất tài khoản
     *
     * @param Request $request http request
     * @return //
     */
    public function logout(Request $request) {
        try {
            $request->session()->forget('expire_time');
            $request->session()->flush();
            return response()->json(['status'=>ServiceStatus::Success]);
        }
        catch (\Exception $e) {
            return response()->json(['status'=>ServiceStatus::Error, 'message'=>$e->getMessage()]);
        }
    }

    /**
     * Kiểm tra session hết hạn
     */
    public function checkSessionExpire(Request $request) {
        $expire_time = $request->session()->get('expire_time');
        $now = Carbon::now();

        if ($now->gt($expire_time)) {
            session()->forget('expire_time');
            session()->flush();
        }

        return $now->gt($expire_time);
    }

    /**
     * Đăng ký tài khoản
     */
    public function register(Request $request) {
        try {
            $user = DB::table('user')
                    ->select('UserID')
                    ->where('UserName', $request->userName)
                    ->first();

            if ($user) {
                return response()->json(['status'=>ServiceStatus::Fail, 'message'=>'Đã tồn tại tài khoản']);
            }

            $uuid = Str::uuid();

            DB::table('user')
            ->insert(['UserID'=>$uuid,
                    'UserName'=>$request->userName,
                    'FullName'=> $request->fullName,
                    'Password'=>Hash::make($request->password),
                    'DateOfBirth'=> $request->dateOfBirth ? date('Y-m-d', strtotime($request->dateOfBirth)) : null,
                    'CreatedDate'=>Carbon::now()]);

            $userRegister = DB::table('user')
            ->select('UserID', 'UserName', 'FullName', 'DateOfBirth')
            ->where('UserID', $uuid)
            ->first();

            return response()->json(['status'=>ServiceStatus::Success, 'data'=>$userRegister]);
        }
        catch (\Exception $e) {
            return response()->json(['status'=>ServiceStatus::Error, 'message'=>$e->getMessage()]);
        }
    }

    /**
     * Thay đổi thông tin tài khoản
     */
    public function updateUserInformation(Request $request) {
        try {
            if ($this->checkSessionExpire($request)) {
                return response()->json(['status'=> ServiceStatus::Fail,'message'=> 'Hết session']);
            }

            $userID = DB::table('user')
                    ->select('UserID')
                    ->where('UserID', $request->userID)
                    ->first();

            if (!$userID) {
                return response()->json(['status'=> ServiceStatus::Fail, 'message'=>'Không tồn tại tài khoản']);
            }

            DB::table('user')
                ->where('UserID', $request->userID)
                ->limit(1)
                ->update(['FullName'=> $request->fullName,
                        'DateOfBirth'=>date('Y-m-d', strtotime($request->dateOfBirth)),
                        'ModifiedDate'=>Carbon::now()]);

            $userChange = DB::table('user')
            ->select('UserID', 'UserName', 'FullName', 'DateOfBirth')
            ->where('UserID', $request->userID)
            ->first();

            return response()->json(['status'=>ServiceStatus::Success, 'data'=>$userChange]);
        }
        catch (\Exception $e) {
            return response()->json(['status'=>ServiceStatus::Error, 'message'=>$e->getMessage()]);
        }
    }

    /**
     * Thay đổi mật khẩu
     */
    public function updateUserPassword(Request $request) {
        try {
            if ($this->checkSessionExpire($request)) {
                return response()->json(['status'=> ServiceStatus::Fail,'message'=> 'Hết session']);
            }

            $oldPassword = $request->oldPassword;

            $newPassword = $request->newPassword;

            $repeatPassword = $request->repeatPassword;

            $user = DB::table('user')
                    ->select('UserID', 'Password')
                    ->where('UserID', $request->userID)
                    ->first();

            if ($user && Hash::check($oldPassword, $user->Password) && $newPassword == $repeatPassword) {
                DB::table('user')
                    ->where('UserID', $request->userID)
                    ->limit(1)
                    ->update(['Password'=> Hash::make($request->newPassword),
                            'ModifiedDate'=>Carbon::now()]);

                $userChange = DB::table('user')
                ->select('UserID', 'UserName', 'FullName', 'DateOfBirth')
                ->where('UserID', $request->userID)
                ->first();

                return response()->json(['status'=>ServiceStatus::Success, 'data'=>$userChange]);
            }

            return response()->json(['status'=>ServiceStatus::Fail, 'data'=>'Thông tin không đúng']);
        }
        catch (\Exception $e) {
            return response()->json(['status'=>ServiceStatus::Error, 'message'=>$e->getMessage()]);
        }
    }
}