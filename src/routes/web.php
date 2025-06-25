<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StaffAuthController;

use App\Http\Controllers\AdminAuthController;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Session;

use App\Http\Controllers\StaffAttendanceController;

use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;




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



// ログインフォームで入力した内容（メールアドレスとパスワード）を送信するとき、/loginにpostリクエストが送られると、AuthControllerのloginメソッドが呼ばれて、ログイン処理が行われる

// 本来はFortifyの為、ルートは必要なしですが、LoginRequestを使用してバリデーションを表示する為、ルーティングを記述

// 独自のルートにすると、バリデーションはLoginRequestにて変更できたがFortifyのログイン機能が使えなくなる為、こちらはNGとする

// Route::post('/login', [AuthController::class, 'login']);


// function = 名前のない関数(Fortifyなどで使える)
// コントローラー管理にするとバリデーションを引き継げないためこの表示方法
Route::get('/admin/login', function () {
    return view('auth.admin_login');
});

// admin(管理者)のFortifyログイン
Route::post('/admin/login', [AuthenticatedSessionController::class, 'store']);

// function = 名前のない関数(Fortifyなどで使える)
// これは一時的
// あとでコントローラを使って表示させる
Route::get('/admin/attendance/list', function () {
    return view('attendance.admin_list');
});




