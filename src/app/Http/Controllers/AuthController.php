<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// Userモデルを読み込む
use App\Models\User;

// LoginRequestを読み込む
use App\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    // startアクションで attendance/start.blade.phpを表示する
    public function start()
    {
        return view('attendance.start');
    }

    // loginアクションで(LoginRequest.phpを読み込んで)auth/login.blade.phpを表示する
    public function login(LoginRequest $request)
    {
        return view('auth.login');
    }


    // registerアクション(Fortify)でregister.blade.php(新規登録画面)のフォームリクエストを読み込む　読み込んだデータをUserテーブルに登録する
    // COACHTECH Laravel演習 1-5 入力内容確認ページの送信 (保存)
    public function register(Request $request)
    {
        $user = $request->only(['name','email','password']);
        User::create($user);
    }




}
