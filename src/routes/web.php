<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StaffAuthController;

use App\Http\Controllers\StaffAttendanceController;

use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

use App\Http\Controllers\AdminAttendanceController;

use App\Http\Controllers\AdminStaffController;




// ↓ (一般ユーザー)

// ミドルウェアにより、未ログインの場合はloginページが表示される
// ログインされていれば、出勤登録画面（一般ユーザー）が表示される
// COACHTECH 3-5 ユーザー認証について学ぼう 14.認証ミドルウェアの作成
Route::middleware('auth')->group(function () {
    Route::get('/', [StaffAuthController::class, 'start']);
});
// 一般ユーザーの勤怠登録画面表示
// ログインされていればページが表示される
Route::middleware('auth')->group(function () {
    Route::post('/attendance/start', [StaffAttendanceController::class, 'start']);
});
// 退勤ボタンを押したら
Route::middleware('auth')->group(function () {
    Route::post('/attendance/end', [StaffAttendanceController::class, 'end']);
});
// 休憩入ボタンを押したら
Route::middleware('auth')->group(function () {
    Route::post('/attendance/break-in', [StaffAttendanceController::class, 'breakIn']);
});
// 休憩戻ボタンを押したら
Route::middleware('auth')->group(function () {
    Route::post('/attendance/break-out', [StaffAttendanceController::class, 'breakOut']);
});
// 一般ユーザーの勤務一覧画面を表示
// {year?}/{month?} これにより年月をURLで受け取れるようにする
Route::middleware('auth')->group(function () {
    Route::get('/staff/attendance/list/{year?}/{month?}', [StaffAttendanceController::class, 'list'])->name('staff.attendance.list');
});
// 一般ユーザーの勤務詳細画面を表示
Route::middleware('auth')->group(function () {
    Route::get('/staff/attendance/{date}', [StaffAttendanceController::class, 'show'])->name('staff.attendance.show');
});
// 勤務修正申請
Route::middleware('auth')->group(function () {
    Route::post('/staff/attendance/{date}', [StaffAttendanceController::class, 'update'])->name('staff.attendance.update');
});
// 申請一覧画面
Route::middleware('auth')->group(function () {
    Route::get('/staff/stamp_correction_request/list', [StaffAttendanceController::class, 'myRequest'])->name('staff.attendance.myRequest');
});




// ↓ (管理者)

// function = 名前のない関数(Fortifyなどで使える)
// コントローラー管理にするとバリデーションを引き継げないためこの表示方法
Route::get('/admin/login', function () {
    return view('auth.admin_login');
});
// admin(管理者)のFortifyログイン
Route::post('/admin/login', [AuthenticatedSessionController::class, 'store']);
// 管理者側の勤怠一覧画面
Route::middleware('auth')->group(function () {
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');
});
// 管理者側の勤怠詳細画面
Route::middleware('auth')->group(function () {
    Route::get('/admin/attendance/show', [AdminAttendanceController::class, 'show'])->name('admin.attendance.show');
});
// 修正ボタンを押したら
Route::middleware('auth')->group(function () {
    Route::post('/admin/attendance/show', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');
});
// 全スタッフ一覧表示
Route::middleware('auth')->group(function () {
    Route::get('/admin/staff/list', [AdminStaffController::class, 'index'])->name('admin.staff.list');
});
// スタッフ個別表示
Route::middleware('auth')->group(function () {
    Route::get('/admin/attendance/staff/{user}/{month?}', [AdminStaffController::class, 'showAttendance'])->name('admin.attendance.staff');
});
// 申請一覧（管理者）〜詳細リンク
Route::middleware('auth')->group(function () {
    route::post('/admin/attendance/approve', [AdminAttendanceController::class, 'approve'])->name('admin.attendance.approve');
});



