<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;



class BreakStartTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ①休憩ボタンが正しく機能するかをテスト
     */
    public function test_user_can_start_break_when_working()
    {
        Carbon::setTestNow('2025-07-18 09:00:00');

        $user = User::factory()->create();
        // ($user)に赤線がつく
        // /** @var \App\Models\User $user */ これによって消えた
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        $today = Carbon::now()->startOfDay();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => Attendance::STATUS_WORKING,
            'clock_in' => $today->copy()->addHours(8),
            'work_date' => $today->toDateString(),
            'created_at' => $today,
            'updated_at' => $today,
        ]);

        $response = $this->get('/');
        $response->assertSeeText('休憩入');

        $this->post('/attendance/break-in');

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
        ]);

        $attendance->refresh();
        $this->assertEquals(Attendance::STATUS_BREAK, $attendance->status);
    }

    // ②休憩は一日に何回でもできるかをテスト
    public function test_user_can_start_multiple_breaks_in_one_day()
    {
        Carbon::setTestNow('2025-07-18 10:00:00');

        $user = User::factory()->create();
        // ($user)に赤線がつく
        // /** @var \App\Models\User $user */ これによって消えた
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        $today = Carbon::now()->startOfDay();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => Attendance::STATUS_WORKING,
            'clock_in' => $today->copy()->addHours(8),
            'work_date' => $today->toDateString(),
            'created_at' => $today,
            'updated_at' => $today,
        ]);

        // 最初の休憩
        $this->post('/attendance/break-in');
        $this->assertDatabaseCount('break_times', 1);

        // 休憩終了をシミュレート（←これが追加！）
        $this->post('/attendance/break-out');

        // 状態リロード
        $attendance->refresh();
        $this->assertEquals(Attendance::STATUS_WORKING, $attendance->status);

        // 画面を再読み込み（ボタン表示のため）
        $this->get('/');

        // 2回目の休憩
        Carbon::setTestNow('2025-07-18 11:00:00'); // 時刻をずらす
        $this->post('/attendance/break-in');
        $this->assertDatabaseCount('break_times', 2);
    }

    // 休憩戻ボタンが正しく機能するかをテスト
    /**
     * ③休憩戻ボタンが正しく機能するかをテスト
     */
    public function test_user_can_end_break()
    {
        Carbon::setTestNow('2025-07-18 10:00:00');

        $user = User::factory()->create();
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        $today = Carbon::now()->startOfDay();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => Attendance::STATUS_BREAK,
            'clock_in' => $today->copy()->addHours(8),
            'work_date' => $today->toDateString(),
            'created_at' => $today,
            'updated_at' => $today,
        ]);

        $break = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->subMinutes(30),
            'created_at' => $today,
            'updated_at' => $today,
        ]);

        // 休憩終了
        $this->post('/attendance/break-out');

        // break_end に値が入っているか確認
        $break->refresh();
        $this->assertNotNull($break->break_end);

        // ステータスが WORKING に戻っていること
        $attendance->refresh();
        $this->assertEquals(Attendance::STATUS_WORKING, $attendance->status);
    }

    // ④休憩戻は一日に何回でもできるかをテスト
    public function test_user_can_end_multiple_breaks_in_one_day()
    {
        Carbon::setTestNow('2025-07-18 10:00:00');

        $user = User::factory()->create();
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        $today = Carbon::now()->startOfDay();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => Attendance::STATUS_WORKING,
            'clock_in' => $today->copy()->addHours(8),
            'work_date' => $today->toDateString(),
            'created_at' => $today,
            'updated_at' => $today,
        ]);

        // --- 1回目の休憩開始 ---
        $this->post('/attendance/break-in');
        $attendance->refresh();
        $this->assertEquals(Attendance::STATUS_BREAK, $attendance->status);

        // --- 1回目の休憩終了 ---
        $this->post('/attendance/break-out');
        $attendance->refresh();
        $this->assertEquals(Attendance::STATUS_WORKING, $attendance->status);

        // --- 2回目の休憩開始 ---
        Carbon::setTestNow('2025-07-18 11:00:00');
        $this->post('/attendance/break-in');
        $attendance->refresh();
        $this->assertEquals(Attendance::STATUS_BREAK, $attendance->status);

        // --- 2回目の休憩終了 ---
        $this->post('/attendance/break-out');
        $attendance->refresh();
        $this->assertEquals(Attendance::STATUS_WORKING, $attendance->status);

        // break_times テーブルに break_end が2件とも入っているか確認
        $this->assertEquals(2, BreakTime::where('attendance_id', $attendance->id)
            ->whereNotNull('break_end')
            ->count());
    }

    // 休憩時刻が勤怠一覧画面で確認できるかをテスト
    public function test_break_duration_is_visible_on_attendance_list()
    {
        Carbon::setTestNow('2025-07-18 10:00:00');

        $user = User::factory()->create();
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        $today = Carbon::today();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => Attendance::STATUS_WORKING,
            'clock_in' => $today->copy()->addHours(8),
            'work_date' => $today->toDateString(),
        ]);

        // 10:00 休憩入
        $this->post('/attendance/break-in');

        // 10:30 休憩戻
        Carbon::setTestNow('2025-07-18 10:30:00');
        $this->post('/attendance/break-out');

        // Blade側の表示が "0:30" ならここも合わせる
        $response = $this->get('/staff/attendance/list');
        $response->assertSeeText('0:30'); // ← 修正ポイント
    }
}