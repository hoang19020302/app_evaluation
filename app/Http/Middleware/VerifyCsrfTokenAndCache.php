<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VerifyCsrfTokenAndCache
{
    public function handle($request, Closure $next)
    {
        // Kiểm tra xem token đã tồn tại trong cache chưa
        $csrfToken = Cache::get('csrf_token');

        if (!$csrfToken || $csrfToken === null) {
            // Nếu không tồn tại, tạo mới token
            $csrfToken = csrf_token();
            // Lưu token vào cache với thời gian hết hạn là 1 giờ
            Cache::put('csrf_token', $csrfToken, now()->addHours(1));
        }

        // Gán token vào request để sử dụng trong các yêu cầu tiếp theo
        $request->merge(['_token' => $csrfToken]);

        return $next($request);
    }
}
