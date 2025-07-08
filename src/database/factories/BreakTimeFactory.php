<?php

namespace Database\Factories;

use App\Models\BreakTime;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakTimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = BreakTime::class;

    public function definition()
    {
        $start = $this->faker->dateTimeBetween('08:00:00', '12:00:00');
        $end = (clone $start)->modify('+1 hour');

        return [
            'attendance_id' => \App\Models\Attendance::inRandomOrder()->first()->id,
            'break_start' => $start->format('Y-m-d H:i:s'), // ← 日付＋時間
            'break_end' => $end->format('Y-m-d H:i:s'),     // ← 日付＋時間
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
