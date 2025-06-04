<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StaffAuthController;

use App\Http\Controllers\AdminAuthController;






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


Route::get('/admin/login', [AdminAuthController::class, 'login']);


