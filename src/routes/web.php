<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

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

Route::middleware(['auth.verified'])
    ->prefix('attendance')
    ->name('attendance.')
    ->group(function () {
        Route::get('/', [AttendanceController::class, 'attendance'])->name('attendance');
        Route::post('/', [AttendanceController::class, 'store'])->name('store');
        Route::get('/list', [AttendanceController::class, 'index'])->name('list');
        Route::get('/detail/{id}', [AttendanceController::class, 'detail'])->name('detail');
        Route::post('/detail/{id}', [ApplicationController::class, 'storeApplication'])->name('storeApplication');
    });

// 管理者
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/attendance/list', [AdminController::class, 'adminAttendanceList'])->name('admin_attendance_list');
        Route::get('/attendance/{id}', [AdminController::class, 'adminAttendanceDetail'])->name('admin_attendance_detail');
        Route::post('/attendance/{id}', [AdminController::class, 'adminStoreAttendance'])->name('admin_storeAttendance');
        Route::get('/attendance/staff/list', [AdminController::class, 'adminStaffList'])->name('admin_staff_list');
        Route::get('/attendance/staff/{id}', [AdminController::class, 'adminAttendanceIndex'])->name('admin_attendance_index');
        Route::get('/stamp_correction_request/list', [AdminController::class, 'AdminApplicationList'])->name('admin_application_list');
        Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminController::class, 'adminApprove'])->name('admin_approve');
        Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminController::class, 'storeAdminApprove'])->name('admin_storeApprove');
    });

Route::GET('/admin/login', [AdminController::class, 'showLoginForm'])->middleware('guest');

Route::middleware(['auth', 'handle.application.list'])
    ->get('/stamp_correction_request/list', [ApplicationController::class, 'applicationList'])
    ->name('attendance.applicationList');
