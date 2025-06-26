<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
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
}
