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

//group token csrf
Route::middleware(['csrf_cache', 'web'])->group(function () {
    // csrf
    Route::get('/csrf-token', [YourController::class, 'getCsrfToken']);
    // token
    Route::get('/tokens', [YourController::class, 'getToken']);
    // register
    Route::post('/register', [RegisterController::class, 'registerUser']);
    // login
    Route::post('/login', [LoginController::class, 'loginUser']);
    // logout
    Route::post('/logout', [LogoutController::class, 'logoutUser']);
    // list-email
    Route::get('/list-email', [ListEmailController::class, 'getEmails']);
    //list-user
    Route::get('/list-user', [ListUserController::class, 'getUsers']);
    //user-info
    Route::get('/user-info/{id}', [ListUserController::class, 'getUser']);
    //group email
    Route::get('/group-email', [GroupEmailController::class, 'getGroupEmail']);
    
});
Route::middleware(['web', 'csrf_cache'])->group(function () {
    // Định nghĩa các tuyến đường API ở đây
    //send email
    Route::post('/send-email', [SendEmailController::class, 'sendEvaluationInvitations']);
});

//group token as sanctum
Route::middleware(['cache_token_auth'])->group(function () {

});

