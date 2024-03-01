<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\CheckFormController;


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
    return redirect('http://172.23.176.1:5500/page.html');
});

//api-status
Route::get('/api-status', [ApiController::class, 'index']);

//failed-login
Route::get('/failed-login', [CheckFormController::class, 'failedLogin'])->name('failed-login');

//error
Route::get('/error', [CheckFormController::class, 'error'])->name('error');

//spirit
Route::get('/spirit', [CheckFormController::class, 'spirit'])->name('spirit')->middleware('check_token_expiration');

//character
Route::get('/character', [CheckFormController::class, 'character'])->name('character')->middleware('check_token_expiration');



