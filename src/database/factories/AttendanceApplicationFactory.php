<?php

namespace Database\Factories;
use Carbon\Carbon;
use App\Models\AttendanceApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    // このファクトリーが生成する対象のモデルはAttendanceApplicationです
    protected $model = AttendanceApplication::class;
    // この中でダミーデータの中身を定義する
    public function definition()
    {
        $start = $this->faker->time('H:i:s');
        $startCarbon = Carbon::createFromFormat('H:i:s', $start);
        $end = $startCarbon->copy()->addMinutes($this->faker->numberBetween(10, 60))->format('H:i:s');

        return [
            // user_id, attendance_id は Seeder 側で渡す
            'before_clock_in' => $this->faker->time('H:i:s'),
            'before_clock_out' => $this->faker->time('H:i:s'),
            'after_clock_in' => $this->faker->time('H:i:s'),
            'after_clock_out' => $this->faker->time('H:i:s'),
            'before_breaks_json' => json_encode(['start' => '12:00:00', 'end' => '12:30:00']),
            'after_breaks_json' => json_encode(['start' => $start, 'end' => $end]),
            'reason' => $this->faker->sentence,
            'status' => $this->faker->numberBetween(0, 1),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

