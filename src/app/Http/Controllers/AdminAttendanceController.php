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
        $workDate = $date ? Carbon::parse($date)->toDateString() : now()->toDateString(); // ←ここ修正！
    
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
}
