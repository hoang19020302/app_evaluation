<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class GroupEmailController extends Controller
{
    //GET group-email
    public function getGroupEmail() {
        $groupEmail = Cache::get('group_info', []);
        if ($groupEmail) {
            return response()->json(['group_info' => $groupEmail]);
        }
        return response()->json(['error' => 'No group email found'], 404);
    }
}
