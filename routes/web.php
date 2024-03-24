<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckTokenController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\NotifyGoogleController;
use App\Http\Controllers\Auth\FacebookController;
use App\Http\Controllers\EmailTrackingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('welcome');
// home
Route::get('/home', function() {
    return view('home');
})->name('home');

//  trạck-email-open
Route::get('/track-email-open', [EmailTrackingController::class, 'trackEmailOpen'])->name('track.email.open');

//check-token
//Route::get('/check-token', [CheckTokenController::class, 'checkToken'])->name('check.token');

//error
Route::get('/error', function() {
    return view('check.error');
})->name('error');
// notify/{state}
Route::get('/notify/{state}', [NotifyGoogleController::class, 'notifyStatus'])->name('handle.notify');
// no-internet
Route::get('/no-internet', [NotifyGoogleController::class, 'noInternet'])->name('no.internet');

// auth/google/login
Route::get('/auth/google/login', [GoogleController::class, 'redirectToGoogleForLogin'])->name('google.login')->middleware('check.internet');

// auth/google/forgot-password
Route::get('/auth/google/forgot-password', [GoogleController::class, 'forgotPasswordGoogle'])->name('google.forgot.password')->middleware('check.internet');

// auth/google/callback
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// auth/facebook/login
Route::get('/auth/facebook/login', [FacebookController::class, 'redirectToFacebookForLogin'])->name('facebook.login')->middleware('check.internet');

// auth/facebook/forgot-password
Route::get('/auth/facebook/forgot-password', [FacebookController::class, 'forgotPasswordFacebook'])->name('facebook.forgot.password')->middleware('check.internet');

// auth/facebook/callback
Route::get('/auth/facebook/callback', [FacebookController::class, 'handleFacebookCallback']);