<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ①退勤ボタンが正しく機能するかをテスト
     */
    public function test_user_can_clock_out_when_working()
    {
        Carbon::setTestNow('2025-07-18 18:00:00');

        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $today = Carbon::today();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => Attendance::STATUS_WORKING,
            'clock_in' => $today->copy()->addHours(9),
            'work_date' => $today->toDateString(),
        ]);

        // 退勤ボタンが画面にあることを確認
        $this->get('/')->assertSee('退勤');

        // 退勤処理
        $this->post('/attendance/end');

        $attendance->refresh();

        // ステータスが「退勤済」に変更されていること
        $this->assertEquals(Attendance::STATUS_DONE, $attendance->status);

        // 退勤時刻が記録されていること
        $this->assertNotNull($attendance->clock_out);
    }

    /**
     * ②退勤時刻が勤怠一覧に表示されているかををテスト
     */
    public function test_clock_out_time_is_visible_on_attendance_list()
    {
        Carbon::setTestNow('2025-07-18 18:00:00');

        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $today = Carbon::today();

        // 出勤・退勤済のデータを作成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => Attendance::STATUS_DONE,
            'clock_in' => $today->copy()->addHours(9),
            'clock_out' => $today->copy()->addHours(18),
            'work_date' => $today->toDateString(),
        ]);

        // 一覧画面に退勤時刻が表示されること
        $this->get('/staff/attendance/list')->assertSee('18:00');
    }
}
