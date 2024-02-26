<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;

class YourController extends Controller
{
    //GET csrf-token
    public function getCsrfToken() {
        $csrfToken = Cache::remember('csrf_token', now()->addHours(1), function () {
            return csrf_token();
        });
    
        return response()->json(['csrf_token' => $csrfToken]);
    }

    //GET token
    public function getToken() {
        $users = Cache::get('users_info', []);

        $usersToken = Cache::get('users_token', []);
        foreach ($users as $user) {
            $usersToken[] = $user['token'];
        }
        return response()->json(['users_token' => $usersToken]);
    }
}