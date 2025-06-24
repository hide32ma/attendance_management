<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

//  Userモデルを読み込む
use App\Models\User;
// Attendanceモデルを読み込む
use App\Models\Attendance;

use App\Models\Attendance_application;

use App\Models\BreakTime;

use Carbon\Carbon;

use Carbon\CarbonPeriod;

// Authファサードを読み込む
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Validator;

use Illuminate\Support\MessageBag;


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

        $current = Carbon::createFromDate($year ?? now()->year, $month ?? now()->month, 1);

        // 月の始まりと終わり
        $startOfMonth = $current->copy()->startOfMonth();
        $endOfMonth = $current->copy()->endOfMonth();

        // 全日付を取得
        $daysInMonth = CarbonPeriod::create($startOfMonth, $endOfMonth);

        // 該当月の出勤データを取得（キーを日付にしておくと便利）
        $attendances = Attendance::where('user_id', $userId)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->with('breakTimes')
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->work_date)->toDateString(); // '2023-06-01' 形式
            });

        return view('attendance.staff_list', compact('daysInMonth', 'attendances', 'current'));
    }

    // 一般ユーザーの勤務詳細画面（動的セグメント）
    // 詳細ページ表示（編集フォームあり）
    public function show($date)
    {
        $user = auth()->user();
        $workDate = Carbon::parse($date)->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $workDate)
            ->first();
            // 存在しない日は null

        return view('attendance.staff_show', [
            'attendance' => $attendance,
            'workDate' => $workDate,
        ]);
    }
    // 修正申請ボタンの処理
    public function update(Request $request, Attendance $attendance)
    {
        // バリデーション（出勤・退勤・備考）
        $validator = Validator::make($request->all(), [
            'clock_in' => 'required',
            'clock_out' => 'required|after:clock_in',
            'reason' => 'required|string',
        ], [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_out.required' => '退勤時間を入力してください',
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'reason.required' => '備考を記入してください',
        ]);

        // カスタムエラー格納用
        $customErrors = new MessageBag();

        // 勤務時間の範囲
        $clockIn = Carbon::parse($request->input('clock_in'));
        $clockOut = Carbon::parse($request->input('clock_out'));

        foreach ($request->input('breaks', []) as $break) {
            if (!empty($break['start']) && !empty($break['end'])) {
                $breakStart = Carbon::parse($break['start']);
                $breakEnd = Carbon::parse($break['end']);

                if ($breakStart->lt($clockIn) || $breakEnd->gt($clockOut)) {
                    $customErrors->add('break_time', '休憩時間が勤務時間外です');
                    break; // 1件でOKなら break で抜ける
                }
            }
        }

        // バリデーション or カスタムエラーがあれば戻る
        if ($validator->fails() || $customErrors->any()) {
            return back()
                ->withErrors($validator->errors()->merge($customErrors))
                ->withInput();
        }

        // 通常処理
        Attendance_application::create([
            'attendance_id' => $attendance->id,
            'applicant_id' => Auth::id(),
            'before_clock_in' => $attendance->clock_in,
            'after_clock_in' => $request->input('clock_in'),
            'before_clock_out' => $attendance->clock_out,
            'after_clock_out' => $request->input('clock_out'),
            'before_breaks_json' => json_encode($attendance->breakTimes),
            'after_breaks_json' => json_encode($request->input('breaks')),
            'reason' => $request->input('reason'),
            'status' => 0,
        ]);

        return redirect()
            ->route('staff.attendance.show', $attendance)
            ->with('message', '修正申請を送信しました。');
    }
}



