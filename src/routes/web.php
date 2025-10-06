<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApplicationController;
use Illuminate\Routing\Route as RoutingRoute;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\AdminAuthenticatedSessionController;

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

Route::middleware(['auth.verified'])->group(function () {
    Route::GET('/attendance', [AttendanceController::class, 'attendance'])->name('attendance.attendance');
    Route::POST('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::GET('/attendance/list', [AttendanceController::class, 'index'])->name('attendance.list');
    Route::GET('/attendance/detail/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    Route::POST('/attendance/detail/{id}', [ApplicationController::class, 'storeApplication'])->name('attendance.storeApplication');
    Route::GET('/stamp_correction_request/list', [ApplicationController::class, 'applicationList'])->name('attendance.applicationList');
});

// 管理者ページ
Route::middleware(['auth', 'admin'])->group(function () {
    Route::GET('/admin/attendance/list', [AdminController::class, 'adminAttendanceList'])->name('admin.admin_attendance_list');
});

Route::GET('/admin/login', [AdminController::class, 'showLoginForm'])->middleware('guest');
