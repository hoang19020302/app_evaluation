<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTokenExpiration
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
        $token = $request->query('token'); // Trích xuất token từ query parameter 'token'

        // Kiểm tra xem token có tồn tại trong cache không
        if (Cache::has($token)) {
            $tokenData = Cache::get($token);
    
            // Kiểm tra tính hợp lệ của token
            if (now()->lt($tokenData['expiration'])) {
                // Token còn hạn, cho phép truy cập
                return $next($request);
            }
        }
    
        // Token không hợp lệ hoặc đã hết hạn, ghi log và chuyển hướng đến trang thông báo lỗi
        return redirect(route('error'));
    }
}
