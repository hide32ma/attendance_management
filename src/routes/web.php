<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StaffAuthController;

use App\Http\Controllers\AdminAuthController;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Session;

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
    return view('attendance.admin_index');
});




