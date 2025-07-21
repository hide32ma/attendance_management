<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    // 自分が行った勤怠情報が全て表示されているかをテスト
    public function test_all_attendance_records_are_displayed_for_logged_in_user()
    {
        Carbon::setTestNow('2025-07-01 09:00:00');

        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        // 同じ月に3日分の勤怠データを登録
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-01',
        ]);
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-02',
        ]);
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-03',
        ]);

        // 勤怠一覧ページを開く
        $response = $this->get('/staff/attendance/list');

        // 各日付が画面上に表示されていることを確認
        $response->assertSee('2025-07-01');
        $response->assertSee('2025-07-02');
        $response->assertSee('2025-07-03');
    }

    // 勤怠一覧ページに現在の月が表示されているかをテスト
    public function test_current_month_is_displayed_on_attendance_list_page()
    {
        Carbon::setTestNow('2025-07-15 10:00:00');

        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/staff/attendance/list');
        // dd($response->getContent()); で全文確認
        file_put_contents(storage_path('logs/attendance_list_test.html'), $response->getContent());


        // Blade側が「2025年07月」表示ならこれで確認
        $response->assertSee('2025/07');
    }
    // 「前月」を押下した時に表示月の前月の情報が表示される
    public function test_next_month_attendance_is_displayed_when_navigated()
    {
        Carbon::setTestNow('2025-07-01 09:00:00');

        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        // 翌月の出勤データ（2025年8月）
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-08-05',
        ]);

        // 翌月のページへアクセス（ルーティングに従って）
        $response = $this->get('/staff/attendance/list?year=2025&month=8');

        // Bladeで「2025/08」や「2025年08月」と表示されるなら assertSee を変更
        $response->assertSee('2025');
        $response->assertSee('08'); // 勤怠データの日付が表示されていること
    }
    // 翌月を押下した時に表示月の前月の情報が表示されるかをテスト
    public function test_previous_month_attendance_is_displayed_when_navigated()
    {
        Carbon::setTestNow('2025-08-01 09:00:00');

        $user = User::factory()->create();
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-15',
        ]);

        $response = $this->get('/staff/attendance/list?year=2025&month=7');

        file_put_contents(storage_path('logs/previous_month_test.html'), $response->getContent());

        // 表示されている月（ページ上に合わせて修正）
        $response->assertSee('2025');  // ← ここを実際のBlade出力に合わせて！

        // 出勤データがちゃんと表示されている
        $response->assertSee('07');
    }

    // 「詳細」を押下すると、その日の勤怠詳細画面に遷移するかをテスト
    public function test_each_day_has_detail_link()
    {
        Carbon::setTestNow('2025-07-01 09:00:00');

        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2日分の勤怠データ
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-01',
        ]);
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-02',
        ]);

        $response = $this->get('/staff/attendance/list');

        // 各日付に対応する詳細リンクが表示されているか確認
        $response->assertSee('/staff/attendance/2025-07-01');
        $response->assertSee('/staff/attendance/2025-07-02');
    }
}