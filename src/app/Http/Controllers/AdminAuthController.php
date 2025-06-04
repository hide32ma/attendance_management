<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// Authファサードを読み込む
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function login()
    {
        return view('auth.admin_login');
    }
}
