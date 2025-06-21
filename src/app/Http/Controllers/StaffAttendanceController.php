<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

//  Userモデルを読み込む
use App\Models\User;
// Attendanceモデルを読み込む
use App\Models\Attendance;

use App\Models\BreakTime;

use Carbon\Carbon;

use Carbon\CarbonPeriod;


class StaffAttendanceController extends Controller
{
    // 出勤ボタンを押した時の処理
    public function start(Request $request)
    {
        $userId = $request->user()->id;
        $today  = now()->toDateString();

        // 🔽 work_dateが今日 または null（勤務外状態） のレコードを探す
        // 今日 or work_dateがnull（登録時に作成されたレコード）を取得
        $attendance = Attendance::where('user_id', $userId)
            ->where(function ($query) use ($today) {
                $query->whereDate('work_date', $today)
                    ->orWhereNull('work_date');
            })
            ->first();

        if ($attendance) {
            // 勤務外じゃないなら出勤させない
            if ($attendance->status !== Attendance::STATUS_OFF) {
                return back()->with('error', 'すでに出勤済みです');
            }

            // work_dateがnullなら今日をセット（この処理がキモ！）
            // 初期レコードならwork_dateを今日に更新
            if (is_null($attendance->work_date)) {
                $attendance->work_date = $today;
            }
        } else {
            // レコードが見つからなければ新規作成
            // 念のためなければ新規作成（通常はここ来ないはず）
            $attendance = new Attendance();
            $attendance->user_id   = $userId;
            $attendance->work_date = $today;
        }

        // 出勤処理
        $attendance->status   = Attendance::STATUS_WORKING;
        $attendance->clock_in = now();
        $attendance->save();

        return back()->with('success', '出勤しました。');
    }

    // 退勤ボタンを押したときの処理
    public function end(Request $request)
    {
        $userId = $request->user()->id;
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('work_date', $today)
            ->first();

        if ($attendance && $attendance->status === Attendance::STATUS_WORKING) {
            $attendance->status = Attendance::STATUS_DONE; // 退勤済にする
            $attendance->clock_out = now();               // 退勤時間を記録
            $attendance->save();
        }

        return back()->with('success', 'お疲れ様でした。');
    }

    // 休憩入ボタンを押した時の処理
    public function breakIn(Request $request)
    {
        $userId = $request->user()->id;
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('work_date', $today)
            ->first();

        if ($attendance && $attendance->status === Attendance::STATUS_WORKING) {
            // 休憩レコード作成
            $attendance->breakTimes()->create([
                'break_start' => now(),
            ]);

            // ステータス変更
            $attendance->status = Attendance::STATUS_BREAK;
            $attendance->save();
        }

        return back()->with('success', '休憩に入りました。');
    }

    // 休憩戻ボタンを押した時の処理
    public function breakOut(Request $request)
    {
        $userId = $request->user()->id;
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('work_date', $today)
            ->first();

        if ($attendance && $attendance->status === Attendance::STATUS_BREAK) {
            // 最後の休憩レコードを取得
            $latestBreak = $attendance->breakTimes()->latest()->first();

            if ($latestBreak && is_null($latestBreak->break_end)) {
                $latestBreak->break_end = now();
                $latestBreak->save();
            }

            $attendance->status = Attendance::STATUS_WORKING;
            $attendance->save();
        }

        return back()->with('success', '休憩が終わりました。');
    }

    // 一般ユーザーの勤務一覧画面
    public function list($year = null, $month = null)
    {
        $userId = auth()->id();
        $year = $year ?? Carbon::now()->year;
        $month = $month ?? Carbon::now()->month;

        $attendances = Attendance::where('user_id', $userId)
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $month)
            ->orderBy('work_date', 'asc')
            ->get();

        $current = Carbon::createFromDate($year, $month, 1);

        return view('attendance.staff_list', compact('attendances', 'current'));
    }
}


