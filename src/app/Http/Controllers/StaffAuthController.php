<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// Userモデルを読み込む
use App\Models\User;
// Attendanceモデルを読み込む
use App\Models\Attendance;

use Carbon\Carbon;






// StaffLoginRequestを読み込む
use App\Http\Requests\StaffLoginRequest;

class StaffAuthController extends Controller
{
    // startアクションで attendance/start.blade.phpを表示する
    public function start()
    {
        // 現在ログインしているユーザーのattendancesテーブルのデータを読み込む
        // $attendances = Attendance::where('user_id', auth()->id())->with('user')->get();

        $today = Carbon::today();
        $attendances = Attendance::where('user_id', auth()->id())->whereDate('work_date',$today)->with('user')->get();
        return view('attendance.staff_start', ['attendances' => $attendances]);
    }
    


    // loginアクションで(LoginRequest.phpを読み込んで)auth/staff_login.blade.phpを表示する
    public function login(StaffLoginRequest $request)
    {
        return view('auth.staff_login');
    }

    // registerアクション(Fortify)でstaff_register.blade.php(新規登録画面)のフォームリクエストを読み込む　読み込んだデータをUserテーブルに登録する
    // COACHTECH Laravel演習 1-5 入力内容確認ページの送信 (保存)
    public function register(Request $request)
    {
        $user = $request->only(['name','email','password']);
        user::create($user);
    }









}
