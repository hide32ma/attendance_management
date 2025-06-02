<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;







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
Route::middleware('auth')->group(function () {
    Route::get('/', [AuthController::class, 'start']);
});

// ログインフォームで入力した内容（メールアドレスとパスワード）を送信するとき、/loginにpostリクエストが送られると、AuthControllerのloginメソッドが呼ばれて、ログイン処理が行われる
Route::post('/login', [AuthController::class, 'login']);

