<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

use App\Models\Attendance_application;

// Authファサードを読み込む
use Illuminate\Support\Facades\Auth;

class AdminAttendanceController extends Controller
{
    // 管理者の勤怠一覧画面
    public function index(Request $request)
    {
        $date = $request->input('date'); // クエリパラメータ ?date=2023-06-01 など
        $targetDate = $date ? Carbon::parse($date) : Carbon::today();

        // 当日の出勤データを取得
        $users = User::with(['attendances' => function ($query) use ($targetDate) {
            $query->whereDate('work_date', $targetDate);
        }, 'attendances.breakTimes'])->get();


        return view('attendance.admin_list', [
            'users' => $users,
            'workDate' => $targetDate->toDateString(),
            'prevDate' => $targetDate->copy()->subDay()->toDateString(),
            'nextDate' => $targetDate->copy()->addDay()->toDateString(),
            'attendances' => $users->flatMap(function ($user) {
                    return $user->attendances;
                }),
                'targetDate' => $targetDate,
        ]);
    }

    // 管理者側の勤怠詳細画面(詳細リンクを押したら表示されるページ)
    public function show(Request $request)
    {
        $userId = $request->input('user_id');
        $date = $request->input('date');
        $workDate = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();

        $targetUser = User::find($userId);

        $attendance = Attendance::where('user_id', $userId)
            ->where('work_date', $workDate)
            ->first();

        $application = null;
        if ($attendance) {
            $application = Attendance_application::where('user_id', $userId)
                ->where('attendance_id', $attendance->id)
                ->where('status', 0)
                ->first();
        }

        return view('attendance.staff_show', [
            'attendance' => $attendance,
            'workDate' => $workDate,
            'application' => $application,
            'user' => $targetUser,
        ]);
    }

    // 修正ボタンを押したら
    /*
    public function update(Request $request)
    {
        // dd($request->all());

        // 勤怠データを取得
        $attendance = Attendance::find($request->input('attendance_id'));



        $request->validate([
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end' => ['nullable', 'date_format:H:i'],
            'reason' => ['required', 'string'],
        ]);

        $clockIn = Carbon::parse($request->clock_in);
        $clockOut = Carbon::parse($request->clock_out);

        // 出退勤の整合性チェック
        if ($clockIn->gt($clockOut)) {
            return back()->withErrors(['clock_in' => '出勤時間もしくは退勤時間が不適切な値です'])->withInput();
        }

        // 休憩時間の整合性チェック
        if ($request->has('breaks')) {
            foreach ($request->breaks as $break) {
                if (!empty($break['start']) && !empty($break['end'])) {
                    $breakStart = Carbon::parse($break['start']);
                    $breakEnd = Carbon::parse($break['end']);

                    if ($breakStart->lt($clockIn) || $breakEnd->gt($clockOut)) {
                        return back()->withErrors(['break_time' => '休憩時間が勤務時間外です'])->withInput();
                    }
                }
            }
        }



        // ここに実際の更新処理を書く...

        // Attendance_Application::create([
            // 'user_id' => $request->input('user_id') ?? Auth::id(), // ここがポイント
            // 'user_id' => $attendance->user_id,
            // 'attendance_id' => $request->input('attendance_id'), // 勤怠ID（必要なら）
            // 'clock_in' => $request->input('clock_in'),
            // 'clock_out' => $request->input('clock_out'),
            // 'breaks_json' => json_encode($request->input('breaks')), // 休憩
            // 'reason' => $request->input('reason'),
            // 'status' => 0, // 承認待ち
        // ]);


        Attendance_Application::create([
            'attendance_id' => $attendance->id,
            'user_id' => $attendance->user_id,
            'applicant_id' => Auth::id(),

            // 修正前のデータ
            'before_clock_in' => $attendance->clock_in,
            'before_clock_out' => $attendance->clock_out,
            'before_breaks_json' => json_encode($attendance->breakTimes->map(function ($break) {
                return [
                    'start' => \Carbon\Carbon::parse($break->break_start)->format('H:i'),
                    'end' => \Carbon\Carbon::parse($break->break_end)->format('H:i'),
            ];
        })),

            // 修正後のデータ
            'after_clock_in' => $request->input('clock_in'),
            'after_clock_out' => $request->input('clock_out'),
            'after_breaks_json' => json_encode($request->input('breaks')),

            // 理由とステータス
            'reason' => $request->input('reason'),
            'status' => 0, // 承認待ち
        ]);
                return redirect()->back()->with('message', '修正を保存しました。');
    }
    */

    //お試しだけどこれで今はOK
    // (修正ボタンを押したら)
    public function update(Request $request)
    {
        // 勤怠データを取得（存在しない可能性がある）
        $attendance = Attendance::find($request->input('attendance_id'));

        // バリデーション
        $request->validate([
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end' => ['nullable', 'date_format:H:i'],
            'reason' => ['required', 'string'],
        ]);

        // 勤怠が存在しない場合のためのデフォルト値
        $attendanceId = $attendance ? $attendance->id : null;
        $userId = $attendance ? $attendance->user_id : $request->input('user_id');

        // 元データ（あれば）を記録
        $beforeClockIn = $attendance ? $attendance->clock_in : null;
        $beforeClockOut = $attendance ? $attendance->clock_out : null;
        $beforeBreaksJson = $attendance && $attendance->breakTimes
            ? json_encode($attendance->breakTimes->map(function ($break) {
                return [
                    'start' => \Carbon\Carbon::parse($break->break_start)->format('H:i'),
                    'end' => \Carbon\Carbon::parse($break->break_end)->format('H:i'),
                ];
            })) : json_encode([]);

        // 修正後のデータ
        $afterClockIn = $request->input('clock_in');
        $afterClockOut = $request->input('clock_out');
        $afterBreaksJson = json_encode($request->input('breaks', []));
        $reason = $request->input('reason');

        // 登録
        Attendance_Application::create([
            'attendance_id' => $attendanceId,
            'user_id' => $userId,
            'applicant_id' => Auth::id(),
            'before_clock_in' => $beforeClockIn,
            'before_clock_out' => $beforeClockOut,
            'before_breaks_json' => $beforeBreaksJson,
            'after_clock_in' => $afterClockIn,
            'after_clock_out' => $afterClockOut,
            'after_breaks_json' => $afterBreaksJson,
            'reason' => $reason,
            'status' => 0, // 未承認
        ]);

        return redirect()->back()->with('message', '修正を保存しました。');
    }

}
