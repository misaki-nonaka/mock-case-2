<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;

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

// FormRequest適用のため、ルートを上書き
Route::post('/login', [AuthController::class, 'login']);


Route::middleware(['auth', 'verified'])->group(function(){
    Route::get('/attendance', [AttendanceController::class, 'attendance']);
    Route::post('/attendance/register', [AttendanceController::class, 'register']);
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('list');
    Route::get('/attendance/detail/{attendance_id}'. [AttendanceController::class, 'detail']);
});

