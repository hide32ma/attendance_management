<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceApplication;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceApplicationsTableSeeder extends Seeder
{
    public function run()
    {
        $attendances = Attendance::with('breakTimes')->get();

        // ランダム理由の候補を用意
        $reasons = [
            '電車の遅延のため',
            '体調不良のため',
            '家庭の事情により',
            '会議が長引いたため',
            'システムトラブル対応のため',
            '寝坊しました…',
            '道が混んでいたため',
            '忘れ物を取りに帰りました'
        ];

        foreach ($attendances as $attendance) {
            $status = rand(0, 1);

            $afterClockIn = null;
            $afterClockOut = null;
            $afterBreaks = null;

            if ($status === 1) {
                $afterClockIn = Carbon::parse($attendance->clock_in)->format('H:i:s');
                $afterClockOut = Carbon::parse($attendance->clock_out)->format('H:i:s');
                $afterBreaks = json_encode($attendance->breakTimes->map(function ($break) {
                    return [
                        'start' => Carbon::parse($break->break_start)->format('H:i:s'),
                        'end' => Carbon::parse($break->break_end)->format('H:i:s'),
                    ];
                }));
            }

            AttendanceApplication::create([
                'user_id' => $attendance->user_id,
                'attendance_id' => $attendance->id,
                'before_clock_in' => Carbon::parse($attendance->clock_in)->format('H:i:s'),
                'before_clock_out' => Carbon::parse($attendance->clock_out)->format('H:i:s'),
                'before_breaks_json' => json_encode($attendance->breakTimes->map(function ($break) {
                    return [
                        'start' => Carbon::parse($break->break_start)->format('H:i:s'),
                        'end' => Carbon::parse($break->break_end)->format('H:i:s'),
                    ];
                })),
                'after_clock_in' => $afterClockIn,
                'after_clock_out' => $afterClockOut,
                'after_breaks_json' => $afterBreaks,
                'reason' => $reasons[array_rand($reasons)], // ← ここがランダム！
                'status' => $status,
            ]);
        }
    }
}