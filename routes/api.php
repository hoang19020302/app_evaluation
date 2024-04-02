<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\YourController;
use App\Http\Controllers\API\ListEmailController;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\ListUserController;
use App\Http\Controllers\API\SendEmailController;
use App\Http\Controllers\API\GroupEmailController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\LogoutController;
use App\Http\Controllers\API\CheckInfoController;
use App\Http\Controllers\API\ChangePasswordController;
use App\Http\Controllers\API\GetUserInfoGoogleController;
use App\Http\Controllers\API\ResetPasswordController;
use App\Http\Controllers\API\PermissionAdminController;



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
// analytics
Route::get('/permission/admin', [PermissionAdminController::class, 'permissionAdmin']);

// email-no-register
Route::post('/email-no-register', [PermissionAdminController::class,'emailNoRegister']);

// info-test/{email}
Route::get('/info-test/{email}', [PermissionAdminController::class,'emailInfoTest']);

// GET /info-test/{personalResultID}
Route::get('/detail-info-test/{personalResultID}', [PermissionAdminController::class,'detailInfoTest']);

//POST /api/email-auth
Route::post('/email-auth', [ResetPasswordController::class, 'emailAuth']);

// POST /api/verify
Route::post('/verify-code', [ResetPasswordController::class, 'verifyCode']);

// POST /api/repeat-code
Route::post('/repeat-code', [ResetPasswordController::class, 'repeatCode']);

// POST /api/not-google-email
Route::post('/not-google-email', [ResetPasswordController::class, 'notGoogleEmail']);

// POST /api/reset-password
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword']);

//GET /google/user-info
Route::get('/google/user-info', [GetUserInfoGoogleController::class, 'getUserInfoGoogle']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Định nghĩa các tuyến đường API ở đây
// csrf
Route::get('/csrf-token', [YourController::class, 'getCsrfToken']);
// register
Route::post('/register', [RegisterController::class, 'registerUser']);
// login
Route::post('/login', [UserController::class, 'login']);



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
    Route::post('/list-email', [ListEmailController::class, 'getEmails']);
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

