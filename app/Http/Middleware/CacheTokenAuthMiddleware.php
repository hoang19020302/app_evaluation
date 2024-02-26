<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CacheTokenAuthMiddleware
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
       // Kiểm tra xem token được gửi từ phía client có tồn tại trong cache không
       $token = $request->bearerToken();
       $userInfoList = Cache::get('users_info');

       if (!$token || !$userInfoList) {
           return response()->json(['error' => 'Unauthorized'], 401);
       }

       // Kiểm tra xem token có khớp với token trong danh sách người dùng không
       $user = collect($userInfoList)->first(function ($user) use ($token) {
           return $user['token'] === $token;
       });

       if (!$user) {
           return response()->json(['error' => 'Unauthorized'], 401);
       }

       // Nếu token hợp lệ, tiếp tục xử lý request
       return $next($request);
    }
}
