<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\DB;
use App\Mail\UserInformation;
use App\Mail\UserNewPassword;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Facebook\Facebook;
use Illuminate\Support\Facades\Http;

class FacebookController extends Controller
{
    //GET auth/register
    public function redirectToFacebookForRegister() {
        return Socialite::driver('facebook')
            ->with(['state' => 'register', 'display' => 'touch'])//'auth_type' => 'select_account', 'access_type' => 'offline', hd: 'vnu.edu.vn',
            ->redirect();
    }

    // GET auth/login
    public function redirectToFacebookForLogin() {
        return Socialite::driver('facebook')
            ->with(['state' => 'login', 'display' => 'touch'])
            ->redirect();
    }

    // GET auth/google/callback
    public function handleFacebookCallback(Request $request) {
        $state = $request->query('state');
        $facebookUser = Socialite::driver('facebook')->stateless()->user();
        $email = $facebookUser->getEmail();
        $name = $facebookUser->getName();
        $recipientId = $facebookUser->getId();
        $birthday = $facebookUser->user['birthday'] ?? null;
        $accessToken = $facebookUser->token;

        switch ($state) {
            case 'register':
                $date = Carbon::now();
                $password = Str::random(8);
                $expire_time = Carbon::now()->addMinutes(Config::get('session.lifetime'));
                session()->regenerate();
                session()->put('expire_time', $expire_time);
                $message = 'Bạn đã đăng ký thành công tomatch.me, mật khẩu của bạn là:' . $password. '. Bạn hãy dùng nó để đăng nhập vào app nếu như bạn ko muốn dùng Fb để đăng nhập. Tôi khuyên bạn nên đổi mật khẩu này để đảm bảo tính bảo mật.Trân trọng! ';
                $this->sendFacebookMessage($recipientId, $message, $accessToken);
                return response()->json(['state' => $state, 'name' => $name, 'email' => $email, 'password' => $password, 'birthday' => $birthday, 'sessionID' => session()->getId(), 'id' => $recipientId, 'accessToken' => $accessToken]);
                break;
            case 'login':
                $expire_time = Carbon::now()->addMinutes(Config::get('session.lifetime'));
                session()->regenerate();
                session()->put('expire_time', $expire_time);
                $message = 'Đăng nhập thành công. Chào mừng bạn đến với tomatch.me!!!';
                $this->sendFacebookMessage($recipientId, $message, $accessToken);
                //return redirect()->route('notify.login')
                //return redirect()->route('handle.notify', ['state' => $state])->with(['state' => $state, 'title' => 'Thành công!', 'message' => 'Đăng nhap thành công', 'success' => 'alert-success', 'url' => route('home')]);
                return response()->json(['state' => $state, 'name' => $name, 'email' => $email, 'birthday' => $birthday, 'sessionID' => session()->getId(), 'accessToken' => $accessToken, 'id' => $recipientId]);
                break;
            default:
                return redirect()->route('handle.notify', ['state' => 'unknown']);
        }
    }
    public function sendFacebookMessage($recipientId, $message, $accessToken) {
        $url = "https://graph.facebook.com/v19.0/me/messages?access_token={$accessToken}";
        $response = Http::post($url, [
            'recipient' => ['id' => $recipientId],
            'message' => ['text' => $message]
        ]);
    }
}
