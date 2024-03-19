<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\ServiceStatus;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;


class GetUserInfoGoogleController extends Controller
{
    //GET /api/google/user-info
    public function getUserInfoGoogle(Request $request) {
        $method = $request->query('method');
        $type = $request->query('type');
        if ($method === 'google') {
            $userInfo = Cache::get('user_' . $method . '_' . $type);
            Cache::forget('user_' . $method . '_' . $type);
            return response()->json(['status' => ServiceStatus::Success, 'userInfo' => $userInfo]);
        }
    }
}
