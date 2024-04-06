<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Enums\ServiceStatus;

class AuthenticateSessionId
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
        $expectedSessionId = $request->header('X-Session-ID');

        if (!$expectedSessionId || $expectedSessionId !== session()->getId()) {
            return response()->json(['status' => ServiceStatus::Error, 'message' => 'Session ID không hợp lệ']);
        }

        return $next($request);
    }
}
