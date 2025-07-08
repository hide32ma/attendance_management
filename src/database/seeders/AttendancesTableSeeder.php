<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();

        $startDate = Carbon::create(2025, 6, 1);
        $endDate = Carbon::create(2025, 8, 31);

        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        $dates = collect($period)->map(fn($date) => $date->format('Y-m-d'));

        foreach ($users as $user) {
            // 日付をランダムに並び替え
            $randomDates = $dates->shuffle()->take(30); // 各ユーザー30日分

            foreach ($randomDates as $dateStr) {
                $date = Carbon::parse($dateStr);

                Attendance::create([
                    'user_id' => $user->id,
                    'work_date' => $dateStr,
                    'clock_in' => $date->copy()->setTime(rand(7, 9), rand(0, 59)),
                    'clock_out' => $date->copy()->setTime(rand(17, 19), rand(0, 59)),
                    'status' => rand(0, 1),
                ]);
            }
        }
    }
}

