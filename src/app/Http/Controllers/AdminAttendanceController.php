<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

use App\Models\AttendanceApplication;


use Illuminate\Support\MessageBag;

use Illuminate\Support\Facades\Validator;

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
            $application = AttendanceApplication::where('user_id', $userId)
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


    // (修正ボタンを押したら)
    public function update(Request $request)
    {
        // 勤怠データを取得（存在しない可能性がある）
        $attendance = Attendance::find($request->input('attendance_id'));

        // バリデーション
        $validator = Validator::make($request->all(), [
            'clock_in' => 'required|',
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
            if (
                !empty($break['start']) && !empty($break['end']) &&
                preg_match('/^\d{2}:\d{2}$/', $break['start']) &&
                preg_match('/^\d{2}:\d{2}$/', $break['end'])
            ) {

                try {
                    $breakStart = Carbon::parse($break['start']);
                    $breakEnd = Carbon::parse($break['end']);

                    if ($breakStart->lt($clockIn) || $breakEnd->gt($clockOut)) {
                        $customErrors->add('break_time', '休憩時間が勤務時間外です');
                        break;
                    }
                } catch (\Exception $e) {
                    $customErrors->add('break_time', '休憩時間の形式が正しくありません');
                    break;
                }
            }
        }

        // バリデーション or カスタムエラーがあれば戻る
        if ($validator->fails() || $customErrors->any()) {
            return back()
                ->withErrors($validator->errors()->merge($customErrors))
                ->withInput();
        }

        // work_date を基に日付を取得
        $date = $request->input('date');

        // 申請データを取得
        $application = AttendanceApplication::where('user_id', $request->user_id)
            ->whereHas('attendance', function ($query) use ($request) {
                $query->whereDate('work_date', $request->date);
            })
            ->first();

        if ($application) {
            $attendance = Attendance::find($application->attendance_id);

            // 申請された時刻と日付を組み合わせて Carbon オブジェクトに変換
            $clockIn = Carbon::parse("{$date} {$application->after_clock_in}");
            $clockOut = Carbon::parse("{$date} {$application->after_clock_out}");

            // 保存
            $attendance->clock_in = $clockIn;
            $attendance->clock_out = $clockOut;
            $attendance->save();

            // ステータス更新
            $application->status = 1;
            $application->save();

            return redirect()->back()->with('message', '承認しました');
        }

    }
}
