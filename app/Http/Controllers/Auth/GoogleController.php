<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Mail\UserInformation;
use App\Mail\UserNewPassword;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use App\Enums\ServiceStatus;
use Illuminate\Support\Facades\Cache;
use stdClass;

class GoogleController extends Controller
{
    // GET auth/login
    public function redirectToGoogleForLogin() {
        return Socialite::driver('google')
            ->with(['state' => 'login', 'prompt' => 'consent'])
            ->redirect();
    }

    // GET auth/forgot-password
    public function forgotPasswordGoogle() {
        return Socialite::driver('google')
            ->with(['state' => 'forgot-password', 'prompt' => 'select_account'])
            ->redirect();
    }

    // GET auth/google/callback
    public function handleGoogleCallback(Request $request) {
        $state = $request->query('state');
        // Lấy thông tin từ google 
        $googleUser = Socialite::driver('google')->stateless()->user();
        $email = $googleUser->getEmail();
        $name = $googleUser->getName();
        $birthday = $googleUser->user['birthday'] ?? null;

        switch ($state) {
            // Khi user đăng nhập bằng google
            case 'login':
                $user = DB::table('user')
                        ->select('UserID', 'CreatedDate')
                        ->where('UserName', $email)
                        ->first();
                if ($user) {
                    $expire_time = Carbon::now()->addMinutes(Config::get('session.lifetime'));
                    session()->regenerate();
                    session()->put('expire_time', $expire_time);
                    $message = 'Đăng nhập thành công. Chào mừng bạn đến với tomatch.me!!!';
                    // Lưu này thong tin này vào cache
                    $userData = new stdClass();
                    $userData->userID = $user->UserID;
                    $userData->userName = $email;
                    $userData->method = 'google';
                    $userData->type = 'existing';
                    $userData->sessionID = session()->getId();
                    $userData->createdDate = $user->CreatedDate;
                    Cache::put('user_' . $userData->method . '_' . $userData->type, $userData, 120);

                    Auth::loginUsingId($user->UserID);
                
                    return redirect()->route('handle.notify', ['state' => $state])->with([
                        'state' => $state,
                        'title' => 'Success!',
                        'message' => 'Đăng nhập thành công. Chào mừng bạn đến với tomatch.me!',
                        'modifier' => 'alert-success', 
                        'url' => 'http://localhost:3000/personal-results',
                        'sessionId' => session()->getId(),
                    ]);
                } else {
                    //Lưu thông tin user mới vào csdl
                    $appPassword = Str::random(6);
                    DB::table('user')
                        ->insert(['UserID'=>Str::uuid(),
                                  'UserName'=> $email,
                                  'FullName'=> $name,
                                  'Password'=> Hash::make($appPassword),
                                  'DateOfBirth'=> $birthday,
                                  'CreatedDate'=>Carbon::now()]);
                    // Lấy ra thông tin  user vừa tạo
                    $newUser = DB::table('user')->where('UserName', $email)->first();
                    // Kiểm tra xem user đc tạo chưa
                    if ($newUser) {
                        // Gui email cho người dùng
                        Mail::to($email)->send(new UserInformation($newUser->UserName, $newUser->FullName, $appPassword, $newUser->CreatedDate));
                        // Đăng nhập người dùng mới và tạo phiên
                        $expire_time = Carbon::now()->addMinutes(Config::get('session.lifetime'));
                        session()->regenerate();
                        session()->put('expire_time', $expire_time);
                        // Lưu này thong tin này vào cache
                        $userData = new stdClass();
                        $userData->userID = $newUser->UserID;
                        $userData->userName = $email;
                        $userData->method = 'google';
                        $userData->type = 'new';
                        $userData->sessionID = session()->getId();
                        $userData->createdDate = $newUser->CreatedDate;
                        Cache::put('user_' . $userData->method . '_' . $userData->type, $userData, 120);

                        Auth::loginUsingId($newUser->UserID);
                        return redirect()->route('handle.notify', ['state' => $state])->with([
                                'state' => $state,
                                'title' => 'Success!',
                                'message' => 'Đăng nhập với người dùng mới thành công! Chúng tôi sẽ gửi thông tin đăng nhập cùng với mật khẩu truy cập vào tomatch.me vào gmail của bạn.Vui lòng kiểm tra gmail để xem chi tiết.',
                                'modifier' => 'alert-success', 
                                'url' => 'http://localhost:3000/personal-results',
                                'sessionId' => session()->getId(),
                            ]);
                    }
                } 
                break;
            // Khi người dùng quên mật khẩu
            case 'forgot-password':
                $user = DB::table('user')
                        ->select('FullName', 'UserID')
                        ->where('UserName', $email)
                        ->first();
                // Tạo mới mât mật khẩu nguoi dung
                if ($user) {
                    $newAppPassword = Str::random(6);
                    $updatedTime = Carbon::now();
                    DB::table('user')
                      ->where('UserName', $email)
                      ->update(['Password'=> Hash::make($newAppPassword)]);
                    // Gui email cho người dùng
                    Mail::to($email)->send(new UserNewPassword($user->FullName, $newAppPassword, $updatedTime));
                    return redirect()->route('handle.notify', ['state' => $state])->with([
                        'state' => $state,
                        'title' => 'Success!',
                        'message' => 'Mật khẩu của bạn đã được tạo mới. Vui lòng kiểm tra gmail để lấy mật khẩu và đăng nhập lại vào tomatch.me.',
                        'modifier' => 'alert-success', 
                        'url' => 'http://localhost:3000/',
                    ]);
                    } else {
                        return redirect()->route('handle.notify', ['state' => $state])->with([
                            'state' => $state,
                            'title' => 'Warning!',
                            'message' => 'Tài khoản Google này chưa được đăng ký!',
                            'modifier' => 'alert-warning', 
                            'url' => 'http://localhost:3000',
                        ]);
                    }
                break;
            default:
                return redirect()->route('handle.notify', ['state' => 'unknown']);
        }
    }
}