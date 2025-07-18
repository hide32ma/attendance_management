<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceStartTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 出勤ボタンが正しく機能するかをテスト（勤務外の状態から出勤できる）
     */
    public function test_user_can_clock_in_when_not_working()
    {
        $user = User::factory()->create();
        // ($user)に赤線がつく
        // /** @var \App\Models\User $user */ これによって消えた
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        // 出勤ページにアクセスしてボタンがあるか確認
        $response = $this->get('/');
        $response->assertSeeText('出勤'); // ボタンテキスト確認

        // 出勤処理
        $this->post('/attendance/start');

        // DBに出勤レコードが存在し、ステータスが勤務中になっているか確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => Attendance::STATUS_WORKING, // もしくは 1
        ]);
    }

    /**
     * 出勤は一日一回のみ、退勤済みのユーザーには出勤ボタンが表示されないかをテスト
     */
    public function test_user_cannot_see_start_button_if_already_finished()
    {
        $user = User::factory()->create();
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
            'status' => Attendance::STATUS_DONE, // もしくは 3
        ]);

        $response = $this->get('/staff/start');

        // 出勤ボタンが表示されていないことを確認
        $response->assertDontSee('<button type="submit">出勤</button>', false);
    }

    /**
     * 勤務一覧画面に、出勤時刻が正しく表示されているかをテスト
     */
    public function test_clock_in_time_is_visible_on_attendance_list()
    {
        $user = User::factory()->create();
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        $this->post('/attendance/start');

        $response = $this->get('/staff/attendance/list');

        $clockInTime = now()->format('H:i'); // Blade側の出力に合わせて整形
        $response->assertSee($clockInTime);
    }
}
