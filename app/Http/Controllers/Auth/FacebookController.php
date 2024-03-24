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
use stdClass;

class FacebookController extends Controller
{
    // GET auth/facebook/login
    public function redirectToFacebookForLogin() {
        return Socialite::driver('facebook')
            ->with(['state' => 'login', 'display' => 'touch'])
            ->redirect();
    }

    // GET auth/facebook/forgot-password
    public function forgotPasswordFacebook() {
        return Socialite::driver('facebook')
            ->with(['state' => 'forgot-password', 'display' => 'touch'])
            ->redirect();
    }

    // GET auth/facebook/callback
    public function handleFacebookCallback(Request $request) {
        $state = $request->query('state');
        // Lấy thông tin từ facebook 
        $facebookUser = Socialite::driver('facebook')->stateless()->user();
        $email = $facebookUser->getEmail();
        $name = $facebookUser->getName();
        $birthday = $facebookUser->user['birthday'] ?? null;
        $recipientId = $facebookUser->getId();
        //$accessToken = $facebookUser->token;

        switch ($state) {
            // Khi user đăng nhập bằng facebook
            case 'login':
                $user = DB::table('user')
                        ->select('UserID')
                        ->where('UserName', $email)
                        ->first();
                if ($user) {
                    $expire_time = Carbon::now()->addMinutes(Config::get('session.lifetime'));
                    session()->regenerate();
                    session()->put('expire_time', $expire_time);
                    Auth::loginUsingId($user->UserID);

                    return redirect()->route('handle.notify', ['state' => $state])->with([
                        'state' => $state,
                        'title' => 'Success!',
                        'message' => 'Đăng nhập thành công. Chào mừng bạn đến với tomatch.me!',
                        'modifier' => 'success', 
                        'url' => route('home'),
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
                        Auth::loginUsingId($newUser->UserID);
                        return redirect()->route('handle.notify', ['state' => $state, 'idFb' => $recipientId])->with([
                            'state' => $state,
                            'title' => 'Success!',
                            'message' => 'Đăng nhập người dùng mới thành công! Chúng tôi sẽ gửi thông tin đăng nhập cùng với mật khẩu truy cập vào tomatch.me vào email tài khoản Facebook của bạn.Vui lòng kiểm tra email để xem chi tiết.',
                            'modifier' => 'success', 
                            'url' => route('home'),
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
                    return redirect()->route('handle.notify', ['state' => $state, 'idFb' => $recipientId])->with([
                        'state' => $state,
                        'title' => 'Success!',
                        'message' => 'Mật khẩu của bạn đã được tạo mới. Vui lòng kiểm tra email của Facebook để lấy mật khẩu và đăng nhập lại vào tomatch.me.',
                        'modifier' => 'success', 
                        'url' => route('welcome'),
                    ]);
                    } else {
                        return redirect()->route('handle.notify', ['state' => $state, 'idFb' => $recipientId])->with([
                            'state' => $state,
                            'title' => 'Warning!',
                            'message' => 'Tài khoản Facebook này chưa được đăng ký!',
                            'modifier' => 'warning', 
                            'url' => route('welcome'),
                        ]);
                    }
                break;
            default:
                return redirect()->route('handle.notify', ['state' => 'unknown']);
        }
    }
}
