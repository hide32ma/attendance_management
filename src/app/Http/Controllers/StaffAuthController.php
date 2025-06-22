<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// Userモデルを読み込む
use App\Models\User;
// Attendanceモデルを読み込む
use App\Models\Attendance;

use Carbon\Carbon;

use App\Models\BreakTime;




// StaffLoginRequestを読み込む
use App\Http\Requests\StaffLoginRequest;

class StaffAuthController extends Controller
{
    // startアクションで attendance/start.blade.phpを表示する

    public function start()
    {
        $userId = auth()->id();
        $today = Carbon::today()->toDateString();

        // 今日の出勤データがなければ作成
        // firstOrCreate これは「該当レコードがなければ作って、あればそのまま使う」便利な書き方
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $userId, 'work_date' => $today],
            ['status' => Attendance::STATUS_OFF]
        );

        $attendances = Attendance::where('user_id', $userId)
            ->whereDate('work_date', $today)
            ->with('user')
            ->get();

        return view('attendance.staff_start', [
            'attendances' => $attendances,
            'today' => $today
        ]);




    // ↓これだと、毎日の出勤ステータスが表示されてしまうのでNG

    // {
    // $attendances = Attendance::where('user_id', auth()->id())->with('user')->get();
    // return view('attendance.staff_start', ['attendances' => $attendances]);
    // }


    // 当日の出勤データのみが表示されるOK
    // ダミーデータでもログインできる
    // 日付が変わるとログインエラーになる

    // $today = Carbon::today();

    // $attendances = Attendance::where('user_id', auth()->id())
    // ->where(function ($query) use ($today) {
    // $query->whereDate('work_date', $today)->orWhereNull('work_date');
    // })->with('user')->get();

    // return view('attendance.staff_start', ['attendances' => $attendances]);
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
