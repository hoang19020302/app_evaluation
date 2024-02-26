<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;

class ListUserController extends Controller
{
    //GET /list-user
    public function getUsers() {
        $users = Cache::get('users_info', []);
        if ($users) {
            return response()->json(['users_info' => $users]);
        }
        return response()->json(['error' => 'No users found'], 404);
    }

    // GET /list-user/{id}
    public function getUser($id) {
        // Kiểm tra xem dữ liệu từ cache có tồn tại không
        if (Cache::has('users_info')) {
            $users = Cache::get('users_info', []);
            foreach ($users as $user) {
                if ($user['id'] === $id) {
                    return response()->json(['user_info' => $user]);
                }
            }
        }
        return response()->json(['error' => 'User not found'], 404);
    }

}
