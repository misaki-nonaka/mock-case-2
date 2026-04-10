<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\StampController;
use App\Http\Controllers\ExportController;

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
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])->name('detail');
    Route::post('/attendance/request', [RequestController::class, 'register']);
});


Route::get('/admin/login', function(){
    return view('admin.login');
})->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::post('/admin/logout', function(Request $request){
    Auth::guard('admin')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/admin/login');
});

Route::middleware('auth:admin')->group(function(){
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'list'])->name('admin.attendance.list');
    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'detail'])->name('admin.detail');
    Route::post('/admin/attendance/correction', [AdminAttendanceController::class, 'update']);
    Route::get('/admin/staff/list', [AdminAttendanceController::class, 'staff']);
    Route::get('/admin/attendance/staff/{id}', [AdminAttendanceController::class, 'individual'])->name('admin.individual');
    Route::post('/admin/export/{user_id}/{date}', [ExportController::class, 'export']);
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [StampController::class, 'approve'])->name('admin.approve');
    Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [StampController::class, 'stamp']);
});

Route::get('/stamp_correction_request/list', [RequestController::class, 'list'])
    ->middleware('auth:web,admin');