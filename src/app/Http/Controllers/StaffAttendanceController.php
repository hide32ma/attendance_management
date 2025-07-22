<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// Attendanceモデルを読み込む
use App\Models\Attendance;
use App\Models\AttendanceApplication;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
// Authファサードを読み込む
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Redirect;

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

    // 勤務一覧画面
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
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return Redirect::route('staff.attendance.list')
                ->with('error', '不正な日付形式です');
        }

        $user = auth()->user();
        $workDate = Carbon::parse($date)->toDateString();

        $attendance = Attendance::where('user_id', $user->id)->whereDate('work_date', $workDate)->first();



        // 修正申請データの取得（承認待ち）
        $application = null;
        if ($attendance) {
            $application = AttendanceApplication::where('user_id', $user->id)->where('attendance_id', $attendance->id)->where('status', 0)->first();
        }

        return view('attendance.staff_show', [
            'attendance' => $attendance,
            'workDate' => $workDate,
            'application' => $application, // ←これをビューに渡す
        ]);
    }

    // 修正申請の処理
    public function update(Request $request, $date)
    {
        $user = auth()->user();
        $workDate = Carbon::parse($date)->toDateString();

        $attendance = Attendance::firstOrNew([
            'user_id' => $user->id,
            // 'user_id' => $request->input('user_id'),
            'work_date' => $workDate,
        ]);

        // 新しく作成された場合、保存してIDを発番
        if (!$attendance->exists) {
            $attendance->clock_in = null;
            $attendance->clock_out = null;
            $attendance->status = '0';
            $attendance->save();  //これがポイント
        }


    // バリデーション（出勤・退勤・備考）
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


        // 正しい形式の休憩だけを整形して格納する
        $validBreaks = [];

        foreach ($request->input('breaks', []) as $break) {
            if (
                !empty($break['start']) && !empty($break['end']) &&
                preg_match('/^\d{2}:\d{2}$/', $break['start']) &&
                preg_match('/^\d{2}:\d{2}$/', $break['end'])
            ) {

                $validBreaks[] = [
                    'start' => $break['start'],
                    'end' => $break['end'],
                ];
            }
        }

        // 通常処理
        AttendanceApplication::create([
            // 'attendance_id' => $attendance->id,
            'attendance_id' => $attendance->exists ? $attendance->id : null,
            'user_id' => Auth::id(),
            'applicant_id' => Auth::id(),
            // 'user_id' => $request->input('user_id'),
            'before_clock_in' => $attendance->clock_in,
            'after_clock_in' => $request->input('clock_in'),
            'before_clock_out' => $attendance->clock_out,
            'after_clock_out' => $request->input('clock_out'),
            'before_breaks_json' => json_encode($attendance->breakTimes->map(function ($break) {
                return [
                    'start' => \Carbon\Carbon::parse($break->break_start)->format('H:i'),
                    'end' => \Carbon\Carbon::parse($break->break_end)->format('H:i'),
                ];
            })),
            'after_breaks_json' => json_encode($validBreaks), // ←これで整形された休憩時間が保存される
            'reason' => $request->input('reason'),
            'status' => 0,
        ]);


        return redirect()
        ->route('staff.attendance.show', $attendance)
        ->with('message', '修正申請を送信しました。');
    }

    public function myRequest(Request $request)
    {
        // ログインユーザー取得（guardで判定）
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            $isAdmin = true;
        } else {
            $user = Auth::guard('web')->user();
            $isAdmin = false;
        }

        $status = $request->input('status', 'waiting');

        // クエリビルダ作成
        $query = AttendanceApplication::with('user', 'attendance');

        // 管理者でなければログインユーザーに絞る
        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }

        // ステータスによって取得
        if ($status === 'waiting') {
            $waitingApplications = (clone $query)->where('status', 0)->get();

            return view('attendance.staff_my_requests', [
                'status' => 'waiting',
                'waitingApplications' => $waitingApplications,
            ]);
        }

        if ($status === 'approved') {
            $approvedApplications = (clone $query)->where('status', 1)->get();

            return view('attendance.staff_my_requests', [
                'status' => 'approved',
                'approvedApplications' => $approvedApplications,
            ]);
        }
        // その他はwaitingに飛ばす
        return redirect()->route('staff.attendance.myRequest', ['status' => 'waiting']);
    }
}



