<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    // 勤怠詳細画面にログインユーザーの名前が表示されていることをテスト
    public function test_attendance_detail_page_displays_logged_in_users_name()
    {
        Carbon::setTestNow('2025-07-20 09:00:00');

        // ユーザーを作成し、ログイン
        $user = User::factory()->create([
            'name' => '山田 太郎',
        ]);
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        // 勤怠データを作成（当日分）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-20',
        ]);

        // 勤怠詳細ページにアクセス
        $response = $this->get("/staff/attendance/2025-07-20");

        // 名前が表示されていることを確認
        $response->assertSee('山田 太郎');
    }

    // 勤怠詳細画面に選択した日付が表示されていることをテスト
    public function test_attendance_detail_page_displays_selected_date()
    {
        Carbon::setTestNow('2025-07-20 09:00:00');

        $user = User::factory()->create([
            'name' => '山田 太郎',
        ]);
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        // 勤怠データを作成（指定日）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-20',
        ]);

        $response = $this->get('/staff/attendance/2025-07-20');

        // 表示形式に応じて以下を修正（例：2025/07/20、2025年07月20日など）
        $response->assertSee('2025-07-20');
    }
    // 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致しているかをテスト
    // 勤怠詳細画面に出勤・退勤時刻が表示されていることをテスト
    public function test_attendance_detail_page_displays_correct_clock_in_and_out_times()
    {
        Carbon::setTestNow('2025-07-20 09:00:00');

        $user = User::factory()->create([
            'name' => '山田 太郎',
        ]);
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-20',
            'clock_in' => '2025-07-20 09:00:00',
            'clock_out' => '2025-07-20 18:00:00',
        ]);

        $response = $this->get('/staff/attendance/2025-07-20');

        // 出勤・退勤時刻が表示されていることを確認（表示形式に応じて修正）
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
    // 「休憩」にて記されている時間がログインユーザーの打刻と一致しているかをテスト
    // 休憩時間が正しく表示されているかをテスト
    public function test_attendance_detail_page_displays_correct_break_time()
    {
        Carbon::setTestNow('2025-07-20 09:00:00');

        $user = User::factory()->create([
            'name' => '山田 太郎',
        ]);
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-20',
            'clock_in' => '2025-07-20 09:00:00',
            'clock_out' => '2025-07-20 18:00:00',
        ]);

        // 休憩時間（1回目）
        $attendance->breakTimes()->create([
            'break_start' => '2025-07-20 12:00:00',
            'break_end' => '2025-07-20 12:30:00',
        ]);

        // 勤怠詳細ページにアクセス
        $response = $this->get('/staff/attendance/2025-07-20');

        // 表示されていることを確認（表示形式に応じて修正）
        $response->assertSee('12:00');
        $response->assertSee('12:30');
    }

    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示されるかをテスト
    public function test_error_is_shown_when_clock_in_is_after_clock_out()
    {
        Carbon::setTestNow('2025-07-20 09:00:00');

        $user = User::factory()->create();
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-20',
        ]);

        $response = $this->followingRedirects()->post(route('staff.attendance.update', ['date' => '2025-07-20']), [
            'clock_in' => '19:00',
            'clock_out' => '18:00',
            'reason' => 'テスト理由',
        ], [
            'HTTP_REFERER' => '/staff/attendance/2025-07-20',
        ]);

        $response->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }

    // 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示されるかをテスト
    public function test_error_when_break_starts_after_clock_out()
    {
        Carbon::setTestNow('2025-07-20 09:00:00');

        $user = User::factory()->create();
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-20',
            'clock_in' => '2025-07-20 09:00:00',
            'clock_out' => '2025-07-20 18:00:00',
        ]);

        $response = $this->post(route('staff.attendance.update', ['date' => '2025-07-20']), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['start' => '18:30', 'end' => '19:00'],
            ],
            'reason' => 'テスト理由',
        ]);

        $response->assertSessionHasErrors(); //  確実に失敗する状況を検知
    }

    // 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示されるかをテスト
    public function test_error_when_break_ends_after_clock_out()
    {
        Carbon::setTestNow('2025-07-20 09:00:00');

        $user = User::factory()->create();
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-20',
            'clock_in' => '2025-07-20 09:00:00',
            'clock_out' => '2025-07-20 18:00:00',
        ]);

        $response = $this->post(route('staff.attendance.update', ['date' => '2025-07-20']), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['start' => '17:30', 'end' => '18:30'], // ← 終了が退勤より後
            ],
            'reason' => 'テスト理由',
        ]);

        $response->assertSessionHasErrors(); // ←まずはここに変更して確認
    }

    // 備考欄が未入力の場合、エラーメッセージが表示されるかをテスト
    public function test_error_when_reason_is_empty()
    {
        Carbon::setTestNow('2025-07-20 09:00:00');

        $user = User::factory()->create();
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-20',
            'clock_in' => '2025-07-20 09:00:00',
            'clock_out' => '2025-07-20 18:00:00',
        ]);

        $response = $this->post(route('staff.attendance.update', ['date' => '2025-07-20']), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['start' => '12:00', 'end' => '12:30'],
            ],
            'reason' => '', // ← 未入力
        ]);

        $response->assertSessionHasErrors(['reason']);
    }
    // 修正申請処理が実行されるかの確認テスト
    public function test_attendance_application_is_created_and_visible_to_admin()
    {
        Carbon::setTestNow('2025-07-20 09:00:00');

        // 一般ユーザーを作成しログイン
        $user = User::factory()->create();
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        // 勤怠データを作成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-20',
            'clock_in' => '2025-07-20 09:00:00',
            'clock_out' => '2025-07-20 18:00:00',
        ]);

        // 勤怠修正申請（POST）
        $this->post(route('staff.attendance.update', ['date' => '2025-07-20']), [
            'clock_in' => '09:30',
            'clock_out' => '18:30',
            'breaks' => [
                ['start' => '12:00', 'end' => '12:30'],
            ],
            'reason' => '修正テスト',
        ]);

        // 管理者ユーザーでログイン（guard切替に応じて修正）
        $admin = \App\Models\Admin::factory()->create();
        /** @var \App\Models\Admin $admin */
        $this->actingAs($admin, 'admin');

        // 管理者の申請一覧画面にアクセス
        $response = $this->get('/staff/stamp_correction_request/list');


        // 修正申請が表示されていることを確認
        $response->assertSeeText('修正テスト');
        $response->assertSeeText('2025/07/20');
    }

    // 「承認待ち」にログインユーザーが行った申請が全て表示されていることをテスト
    public function test_all_user_applications_are_displayed_in_waiting_list()
    {
        Carbon::setTestNow('2025-07-20 09:00:00');

        // ユーザーを作成しログイン
        $user = User::factory()->create();
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        // 複数日分の出勤データを用意して、各日で申請する
        $dates = ['2025-07-18', '2025-07-19', '2025-07-20'];

        foreach ($dates as $date) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => $date,
                'clock_in' => $date . ' 09:00:00',
                'clock_out' => $date . ' 18:00:00',
            ]);

            // 勤怠修正申請（POST）
            $this->post(route('staff.attendance.update', ['date' => $date]), [
                'clock_in' => '09:30',
                'clock_out' => '18:30',
                'breaks' => [
                    ['start' => '12:00', 'end' => '12:30'],
                ],
                'reason' => '申請テスト ' . $date,
            ]);
        }

        // 管理者ユーザーでログイン（guard切替に応じて修正）
        $admin = \App\Models\Admin::factory()->create();
        /** @var \App\Models\Admin $admin */
        $this->actingAs($admin, 'admin');

        // 管理者の申請一覧画面にアクセス
        $response = $this->get('/staff/stamp_correction_request/list');

        // 各申請が表示されているかを確認
        foreach ($dates as $date) {
            $response->assertSeeText('申請テスト ' . $date);
            $response->assertSeeText(Carbon::parse($date)->format('Y/m/d')); // 表示形式に注意
        }
    }

    // 「承認済み」に管理者が承認した修正申請が全て表示されているかをテスト
    public function test_all_approved_applications_are_displayed_in_approved_list()
    {
        Carbon::setTestNow('2025-07-20 09:00:00');

        $user = User::factory()->create();
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        $dates = ['2025-07-18', '2025-07-19', '2025-07-20'];

        foreach ($dates as $date) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => $date,
                'clock_in' => $date . ' 09:00:00',
                'clock_out' => $date . ' 18:00:00',
            ]);

            $this->post(route('staff.attendance.update', ['date' => $date]), [
                'clock_in' => '09:30',
                'clock_out' => '18:30',
                'breaks' => [
                    ['start' => '12:00', 'end' => '12:30'],
                ],
                'reason' => '承認テスト ' . $date,
            ]);
        }

        $admin = \App\Models\Admin::factory()->create();
        /** @var \App\Models\Admin $admin */
        $this->actingAs($admin, 'admin');

        foreach ($dates as $date) {
            $attendance = Attendance::where('user_id', $user->id)
                ->where('work_date', $date)
                ->first();

            $application = \App\Models\AttendanceApplication::where('user_id', $user->id)
                ->where('attendance_id', $attendance->id)
                ->first();

            $this->post(route('admin.attendance.approve'), [
                'application_id' => $application->id,
            ]);
        }

        $response = $this->get('/staff/stamp_correction_request/list');

        foreach ($dates as $date) {
            $response->assertSeeText('承認テスト ' . $date);
            $response->assertSeeText(Carbon::parse($date)->format('Y/m/d'));
        }
    }

    // 各申請の「詳細」を押下すると申請詳細画面に遷移するかをテスト
    public function test_application_detail_button_navigates_to_detail_page()
    {
        Carbon::setTestNow('2025-07-20 09:00:00');

        $user = User::factory()->create();
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-20',
            'clock_in' => '2025-07-20 09:00:00',
            'clock_out' => '2025-07-20 18:00:00',
        ]);

        $this->post(route('staff.attendance.update', ['date' => '2025-07-20']), [
            'clock_in' => '09:30',
            'clock_out' => '18:30',
            'breaks' => [
                ['start' => '12:00', 'end' => '12:30'],
            ],
            'reason' => '詳細遷移テスト',
        ]);

        $admin = \App\Models\Admin::factory()->create();
        /** @var \App\Models\Admin $admin */
        $this->actingAs($admin, 'admin');

        $application = \App\Models\AttendanceApplication::where('user_id', $user->id)
            ->where('attendance_id', $attendance->id)
            ->first();

        // 遷移先に正常アクセスできることを確認
        $response = $this->get(route('admin.attendance.show', [
            'user_id' => $application->user_id,
            'date' => '2025-07-20',
            'from' => 'approval',
        ]));

        // ページが表示されていること（200 OK）
        $response->assertStatus(200);
    }
}
