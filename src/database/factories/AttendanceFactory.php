<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */

    //  このファクトリーが生成する対象のモデルは Attendance ですよ、という宣言です。
    public function definition()
    {
        $clockIn = $this->faker->dateTimeBetween('08:00:00', '10:00:00');
        $clockOut = (clone $clockIn)->modify('+' . $this->faker->numberBetween(6, 10) . ' hours');

        return [
            // Seederから渡すuser_id, work_dateにはここで触らない
            'clock_in' => $clockIn->format('Y-m-d H:i:s'),
            'clock_out' => $clockOut->format('Y-m-d H:i:s'),
            'status' => $this->faker->randomElement([0, 1]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

