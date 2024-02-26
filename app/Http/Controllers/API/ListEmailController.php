<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;

class ListEmailController extends Controller
{
    //GET /list-email
    public function getEmails() {
        $users = Cache::get('users_info', []);
        
        $emails = Cache::get('emails', []);
        foreach ($users as $user) {
            $emails[] = $user['email'];
        }
        if ($emails) {
            return response()->json(['emails' => $emails]);
        }
        return response()->json(['error' => 'No users found'], 404);
    }

}
