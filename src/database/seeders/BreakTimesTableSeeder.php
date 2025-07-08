<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;


class BreakTimesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // すでにあるattendanceデータに対して紐づける
        $attendances = \App\Models\Attendance::all();

        foreach ($attendances as $attendance) {
            \App\Models\BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
            ]);
        }
    }
}
