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
use Facebook\Facebook;
use stdClass;

class GoogleController extends Controller
{
    //GET auth/register
    public function redirectToGoogleForRegister() {
        return Socialite::driver('google')
            ->with(['state' => 'register', 'prompt' => 'consent'])//'prompt' => 'select_account', 'access_type' => 'offline', hd: 'vnu.edu.vn',
            ->redirect();
    }

    // GET auth/login
    public function redirectToGoogleForLogin() {
        return Socialite::driver('google')
            ->with(['state' => 'login', 'prompt' => 'select_account'])
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
            // Khi user đăng ký bằng google
            case 'register':
                // Kiem tra user đã tồn tại trong csdl chưa.
                $user = DB::table('user')
                        ->select('UserID')
                        ->where('UserName', $email)
                        ->first();
                if ($user) {
                    return redirect()->route('handle.notify', ['state' => $state])->with([
                        'state' => $state,
                        'title' => 'Info!',
                        'message' => 'Tài khoản Google này đã tồn tại. Vui lòng sử dụng gmail khác để đăng ký.',
                        'modifier' => 'alert-info', 
                        'url' => route('welcome'),
                    ]);
                }
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
                    return redirect()->route('handle.notify', ['state' => $state])->with([
                        'state' => $state,
                        'title' => 'Success!',
                        'message' => 'Đăng ký tài khoản thành công!Chúng tôi sẽ gửi thông tin đăng ký vào gmail của bạn bao gồm một mật khẩu ngẫu nhiên được tạo trong trường hợp bạn muốn đăng nhập vào tomatch.me mà không dùng Google.',
                        'modifier' => 'alert-success', 
                        'url' => route('home'),
                        'sessionId' => session()->getId(),
                    ]);
                }
                break;
            // Khi user đăng nhập bằng google
            case 'login':
                $user = DB::table('user')
                        ->select('UserID')
                        ->where('UserName', $email)
                        ->first();
                if ($user) {
                    $expire_time = Carbon::now()->addMinutes(Config::get('session.lifetime'));
                    session()->regenerate();
                    session()->put('expire_time', $expire_time);
                    $message = 'Đăng nhập thành công. Chào mừng bạn đến với tomatch.me!!!';
                    $this->sendWelcomeMessage($email, $message);
                    Auth::loginUsingId($user->UserID);

                    return redirect()->route('handle.notify', ['state' => $state])->with([
                        'state' => $state,
                        'title' => 'Success!',
                        'message' => 'Đăng nhập thành công. Chào mừng bạn đến với tomatch.me!',
                        'modifier' => 'alert-success', 
                        'url' => route('home'),
                        'sessionId' => session()->getId(),
                    ]);
                } else {
                    return redirect()->route('handle.notify', ['state' => $state])->with([
                        'state' => $state,
                        'title' => 'Warning!',
                        'message' => 'Tài khoản Google chưa được đăng ký. Vui lòng đăng ký để tiếp tục.',
                        'modifier' => 'alert-warning', 
                        'url' => route('welcome'),
                    ]);
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
                        'url' => route('welcome'),
                    ]);
                    } else {
                        return redirect()->route('handle.notify', ['state' => $state])->with([
                            'state' => $state,
                            'title' => 'Warning!',
                            'message' => 'Tài khoản Google này chưa được đăng ký!',
                            'modifier' => 'alert-warning', 
                            'url' => route('welcome'),
                        ]);
                    }
                break;
            default:
                return redirect()->route('handle.notify', ['state' => 'unknown']);
        }
    }

    private function sendWelcomeMessage($email, $message)
    {
        
        // Sử dụng Facebook SDK để gửi tin nhắn
        $fb = new Facebook([
            'app_id' => env('FACEBOOK_CLIENT_ID'),
            'app_secret' => env('FACEBOOK_CLIENT_SECRET'),
            'default_graph_version' => 'v19.0',
        ]);

        try {
            // Gửi tin nhắn
            $response = $fb->post("/me/messages", ['recipient' => ['email' => $email], 'message' => ['text' => $message]]);
            // Xử lý response nếu cần

        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            // Xử lý nếu có lỗi từ Facebook Graph API
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            // Xử lý nếu có lỗi SDK
        }
    }
}