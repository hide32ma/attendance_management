<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Attendance;





class AdminStaffController extends Controller
{
    // スタッフ全一覧
    public function index()
    {
        $staffs = User::all();
        return view('attendance.admin_staff_list', compact('staffs'));
    }

    // スタッフ個別表示
    public function showAttendance(User $user, Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $date = Carbon::parse($month);
        $endOfMonth = Carbon::parse($month)->endOfMonth();

        // 月の日付一覧を作る
        $dates = CarbonPeriod::create($startOfMonth, $endOfMonth);

        // 該当ユーザーのその月の出勤データを取得
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->with('breakTimes')
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->work_date)->format('Y-m-d');
            });

        return view('attendance.admin_staff_time_table', [
            'user' => $user,
            'attendances' => $attendances,
            'dates' => $dates,
            'date' => Carbon::parse($month), // ←追加：$monthをCarbonに変換して渡す
        ]);
    }
}
