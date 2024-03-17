<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckInternetConnection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$this->isConnectedToInternet()) {
            // Nếu không có kết nối internet, chuyển hướng người dùng đến trang thông báo lỗi hoặc trang khác
            return redirect()->route('no.internet')->with('message', 'Bạn vui lòng kiểm tra lại kết nối Internet!');
        }

        return $next($request);
    }
    private function isConnectedToInternet() {
        $url = 'https://www.google.com';
        $timeout = 10; // Thời gian chờ tối đa (giây)
    
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
    
        if ($httpCode >= 200 && $httpCode < 300) {
            return true; // Kết nối thành công
        } else {
            return false; // Không thể kết nối
        }
    }
}
