<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\CheckFormController;
use App\Http\Controllers\CheckTokenController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\NotifyGoogleController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function() {
    return view('home');
})->name('home');

Route::get('/no-internet', function() {
    return view('googles.no-internet');
})->name('no.internet');
Route::get('/notify/login', [NotifyGoogleController::class, 'notifyLogin'])->name('notify.login');

Route::get('/notify/register', [NotifyGoogleController::class, 'notifyRegister'])->name('notify.register');

Route::get('/notify/forgot-password', [NotifyGoogleController::class, 'notifyForgotPassword'])->name('notify.forgot-password');

//api-status
Route::get('/api-status', [ApiController::class, 'index']);

//failed-login
Route::get('/failed-login', [CheckFormController::class, 'failedLogin'])->name('failed-login');

//error
Route::get('/error', [CheckFormController::class, 'error'])->name('error');

//check-token?token=
Route::get('/check-token', [CheckTokenController::class, 'checkToken'])->name('check.token');

// auth/register
Route::get('/auth/register', [GoogleController::class, 'redirectToGoogleForRegister'])->name('google.register')->middleware('check.internet');

// auth/login
Route::get('/auth/login', [GoogleController::class, 'redirectToGoogleForLogin'])->name('google.login')->middleware('check.internet');

// auth/forgot-password
Route::get('/auth/forgot-password', [GoogleController::class, 'forgotPasswordGoogle'])->name('google.forgot.password')->middleware('check.internet');

// auth/google/callback
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);



