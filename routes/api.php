<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\YourController;
use App\Http\Controllers\API\ListEmailController;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\ListUserController;
use App\Http\Controllers\API\SendEmailController;
use App\Http\Controllers\API\GroupEmailController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\LogoutController;
use App\Http\Controllers\API\CheckInfoController;
use App\Http\Controllers\API\ChangePasswordController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Định nghĩa các tuyến đường API ở đây
// csrf
Route::get('/csrf-token', [YourController::class, 'getCsrfToken']);
// register
Route::post('/register', [RegisterController::class, 'registerUser']);
// login
Route::post('/login', [LoginController::class, 'loginUser']);



//group token as fake sanctum
//Route::middleware(['cache_token_auth'])->group(function () {
    // api/*
    // token
    Route::get('/tokens', [YourController::class, 'getToken']);
    // logout
    Route::post('/logout', [LogoutController::class, 'logoutUser']);
    // change-password
    Route::post('/change-password', [ChangePasswordController::class, 'changePassword']);
    // list-email
    Route::get('/list-email', [ListEmailController::class, 'getEmails']);
    //list-user
    Route::get('/list-user', [ListUserController::class, 'getUsers']);
    //user-info
    Route::get('/user/{id}', [ListUserController::class, 'getUser']);
    //group email
    Route::get('/group-email', [GroupEmailController::class, 'getGroupEmail']);
    //send email
    Route::post('/send-email', [SendEmailController::class, 'sendEmailWithLink']);
    //check-info
    Route::post('/check-info', [CheckInfoController::class, 'checkInfo']);
//});

