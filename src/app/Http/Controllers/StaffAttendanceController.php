<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

//  Userモデルを読み込む
use App\Models\User;
// Attendanceモデルを読み込む
use App\Models\Attendance;


class StaffAttendanceController extends Controller
{
    // 出勤ボタンを押した時
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

        return back()->with('success', '出勤しました！');
    }
}
