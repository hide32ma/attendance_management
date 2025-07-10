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

        Carbon::setLocale('ja');
        $nowDateTime = Carbon::now()->translatedFormat('Y年n月j日（D）') . "\n" . Carbon::now()->format('H:i');

        return view('attendance.staff_start', [
            'attendances' => $attendances,
            'today' => $today,
            'nowDateTime' => $nowDateTime,
        ]);

    }

    // loginアクションで(LoginRequest.phpを読み込んで)auth/staff_login.blade.phpを表示する
    public function login(StaffLoginRequest $request)
    {
        return view('auth.staff_login');
    }

    // registerアクション(Fortify)でstaff_register.blade.php(新規登録画面)のフォームリクエストを読み込む 読み込んだデータをUserテーブルに登録する
    // COACHTECH Laravel演習 1-5 入力内容確認ページの送信 (保存)
    public function register(Request $request)
    {
        $user = $request->only(['name','email','password']);
        user::create($user);
    }
}
