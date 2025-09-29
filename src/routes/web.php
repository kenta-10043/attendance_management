<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;
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
    Route::get('/attendance', [AttendanceController::class, 'attendance'])->name('attendance.attendance');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
});

// 管理者ページ
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/attendance/list', [AdminController::class, 'adminAttendanceList'])->name('admin.admin_attendance_list');
});

Route::get('/admin/login', [AdminController::class, 'showLoginForm'])->middleware('guest');
