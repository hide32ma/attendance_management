<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

}
