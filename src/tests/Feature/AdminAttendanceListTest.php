<?php

namespace Tests\Feature;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\AttendanceApplication;


class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    // その日になされた全ユーザーの勤怠情報が正確に確認できる
    public function test_admin_can_view_attendance_list_for_the_day()
    {
        Carbon::setTestNow('2025-07-22 10:00:00');

        // 管理者ユーザーを作成
        $admin = Admin::factory()->create();

        // 2人のスタッフユーザーと、それぞれの勤怠情報を作成
        $users = User::factory()->count(2)->create();

        foreach ($users as $user) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => '2025-07-22',
                'clock_in' => '2025-07-22 09:00:00',
                'clock_out' => '2025-07-22 18:00:00',
            ]);
        }

        // 管理者としてログイン
        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.list', ['date' => '2025-07-22']));

        $response->assertStatus(200);

        // 勤怠情報が一覧ページに表示されていることを確認
        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee('09:00');
            $response->assertSee('18:00');
        }
    }
    // 遷移した際に現在の日付が表示されるかをテスト
    public function test_attendance_list_displays_current_date()
    {
        // テスト用に現在日時を固定
        Carbon::setTestNow('2025-07-22 10:00:00');

        // 管理者を作成してログイン
        $admin = Admin::factory()->create();

        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.list', ['date' => '2025-07-22']));

        $response->assertStatus(200);

        // Bladeファイルでの表示形式に応じて日付文字列を変える（例: Y年m月d日）
        $response->assertSee('2025-07-22');
    }

    // 前日」を押下した時に前の日の勤怠情報が表示されるかをテスト
    public function test_attendance_list_displays_previous_day_data()
    {
        Carbon::setTestNow('2025-07-22 10:00:00');

        // 管理者を作成
        $admin = Admin::factory()->create();

        // 前日（2025-07-21）の勤怠データを持つスタッフを作成
        $user = User::factory()->create([
            'name' => '前日ユーザー',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-21',
            'clock_in' => '2025-07-21 08:30:00',
            'clock_out' => '2025-07-21 17:30:00',
        ]);

        // 管理者として「前日」ボタン押下を想定してリクエスト（date=2025-07-21）

        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.list', ['date' => '2025-07-21']));

        $response->assertStatus(200);

        // 前日の勤怠情報が表示されていることを確認
        $response->assertSee('前日ユーザー');
        $response->assertSee('08:30');
        $response->assertSee('17:30');
        $response->assertSee('2025-07-21'); // 日付が表示されているか
    }

    // 「翌日」を押下した時に次の日の勤怠情報が表示されるかをテスト
    public function test_attendance_list_displays_next_day_data()
    {
        Carbon::setTestNow('2025-07-22 10:00:00');

        // 管理者を作成
        $admin = Admin::factory()->create();

        // 翌日（2025-07-23）の勤怠データを持つスタッフを作成
        $user = User::factory()->create([
            'name' => '翌日ユーザー',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-23',
            'clock_in' => '2025-07-23 10:00:00',
            'clock_out' => '2025-07-23 19:00:00',
        ]);

        // 「翌日」ボタン押下を想定し、翌日分のURLにアクセス

        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.list', ['date' => '2025-07-23']));

        $response->assertStatus(200);

        // 翌日の勤怠情報が表示されていることを確認
        $response->assertSee('翌日ユーザー');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('2025-07-23'); // 表示されている日付
    }

    // 勤怠詳細画面に表示されるデータが選択したものになっているかをテスト
    public function test_admin_can_view_selected_attendance_detail()
    {
        Carbon::setTestNow('2025-07-22 10:00:00');

        // 管理者作成
        $admin = Admin::factory()->create();

        // ユーザーと勤怠データ作成
        $user = User::factory()->create([
            'name' => 'テストユーザー',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-21',
            'clock_in' => '2025-07-21 09:00:00',
            'clock_out' => '2025-07-21 18:00:00',
        ]);

        // 管理者として該当日の詳細ページにアクセス

        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.show', [
                'user_id' => $user->id,
                'date' => '2025-07-21',
                'from' => 'list', // or 'approval' などアプリの仕様に合わせて
            ]));

        $response->assertStatus(200);

        // 詳細画面に該当情報が表示されていること
        $response->assertSee('テストユーザー');
        $response->assertSee('2025-07-21');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示されるかをテスト
    public function test_validation_error_when_clock_in_is_after_clock_out()
    {
        Carbon::setTestNow('2025-07-22 10:00:00');

        // 管理者と対象ユーザー・勤怠データを作成
        $admin = Admin::factory()->create();

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-21',
            'clock_in' => '2025-07-21 09:00:00',
            'clock_out' => '2025-07-21 18:00:00',
        ]);

        // 管理者としてログインし、出勤時間 > 退勤時間 のデータで更新リクエストを送信

        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')->post(route('admin.attendance.update', [
            'user_id' => $user->id,
            'date' => '2025-07-21',
        ]), [
            'clock_in' => '19:00',  // ← 出勤時間が後ろ
            'clock_out' => '09:00', // ← 退勤時間が前
            'reason' => 'テスト: 出勤が退勤より遅い',
        ]);

        $response->assertStatus(302); // バリデーションエラー時はリダイレクトされる
        $response->assertSessionHasErrors(['clock_out']); // ← clock_out に関するエラーがある
    }

    // 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_validation_error_when_break_start_is_after_clock_out()
    {
        Carbon::setTestNow('2025-07-22 10:00:00');

        // 管理者とユーザー・勤怠データ作成
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-21',
            'clock_in' => '2025-07-21 09:00:00',
            'clock_out' => '2025-07-21 18:00:00',
        ]);

        // 不正な休憩：退勤時間（18:00）より後の 19:00 に休憩開始
        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')->post(route('admin.attendance.update', [
            'user_id' => $user->id,
            'date' => '2025-07-21',
        ]), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['start' => '19:00', 'end' => '19:30']
            ],
            'reason' => 'テスト: 休憩開始が退勤後',
        ]);

        $response->assertStatus(302); // バリデーションエラーでリダイレクト
        $response->assertSessionHasErrors(['break_time']); // 該当バリデーションエラーを検出
    }

    // 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_validation_error_when_break_end_is_after_clock_out()
    {
        Carbon::setTestNow('2025-07-22 10:00:00');

        // 管理者とユーザー・勤怠データ作成
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-21',
            'clock_in' => '2025-07-21 09:00:00',
            'clock_out' => '2025-07-21 18:00:00',
        ]);

        // 不正な休憩：休憩終了が退勤後の 19:30

        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')->post(route('admin.attendance.update', [
            'user_id' => $user->id,
            'date' => '2025-07-21',
        ]), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['start' => '17:30', 'end' => '19:30'] // ← 終了だけが不正
            ],
            'reason' => 'テスト: 休憩終了が退勤後',
        ]);

        $response->assertStatus(302); // リダイレクト
        $response->assertSessionHasErrors(['break_time']); // break_time にエラー
    }

    // 備考欄が未入力の場合のエラーメッセージが表示される
    public function test_validation_error_when_reason_is_empty()
    {
        Carbon::setTestNow('2025-07-22 10:00:00');

        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-21',
            'clock_in' => '2025-07-21 09:00:00',
            'clock_out' => '2025-07-21 18:00:00',
        ]);

        // 備考(reason)を空にして送信

        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')->post(route('admin.attendance.update', [
            'user_id' => $user->id,
            'date' => '2025-07-21',
        ]), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [],
            'reason' => '', // ← 備考が空
        ]);

        $response->assertStatus(302); // リダイレクト（バリデーションエラー）
        $response->assertSessionHasErrors(['reason']); // 'reason' にバリデーションエラー
    }

    // 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できるかをテスト
    public function test_admin_can_view_all_users_name_and_email()
    {
        // 現在日時を固定（必要であれば）
        Carbon::setTestNow('2025-07-22 10:00:00');

        // 管理者ユーザーを作成
        $admin = Admin::factory()->create();

        // 一般ユーザーを複数作成
        $users = User::factory()->count(3)->create();

        // 管理者としてスタッフ一覧ページにアクセス
        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.staff.list'));

        // レスポンスステータス確認
        $response->assertStatus(200);

        // 各ユーザーの氏名とメールアドレスが表示されているか確認
        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    // 管理者ユーザーが特定ユーザーの勤怠情報を正しく確認できるかをテスト
    public function test_admin_can_view_selected_user_attendance_list()
    {
        Carbon::setTestNow('2025-07-22 10:00:00');

        // 管理者とユーザーを作成
        $admin = Admin::factory()->create();
        $user = User::factory()->create(['name' => 'テストユーザー']);

        // 該当ユーザーの勤怠データを複数作成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-20',
            'clock_in' => '2025-07-20 09:00:00',
            'clock_out' => '2025-07-20 18:00:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-21',
            'clock_in' => '2025-07-21 08:30:00',
            'clock_out' => '2025-07-21 17:45:00',
        ]);

        // 管理者として勤怠一覧ページにアクセス
        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.staff', ['user' => $user->id]));


        $response->assertStatus(200);

        // 勤怠データの内容が画面に表示されていることを確認
        $response->assertSee('テストユーザー');
        $response->assertSee('2025-07-20');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('2025-07-21');
        $response->assertSee('08:30');
        $response->assertSee('17:45');
    }

    // 前月」を押下した時に表示月の前月の情報が表示される
    public function test_previous_month_attendance_data_is_displayed()
    {
        Carbon::setTestNow('2025-07-22 10:00:00');

        $admin = Admin::factory()->create();
        $user = User::factory()->create(['name' => 'テストユーザー']);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-06-15',
            'clock_in' => '2025-06-15 09:00:00',
            'clock_out' => '2025-06-15 18:00:00',
        ]);

        // URLをクエリで指定（route() を使わず直接URL記述）
        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list?date=2025-06-15');

        $response->assertStatus(200);
        $response->assertSee('2025-06-15');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    // 「翌月」を押下した時に表示月の翌月の情報が表示される
    public function test_next_month_attendance_data_is_displayed()
    {
        Carbon::setTestNow('2025-07-22 10:00:00');

        $admin = Admin::factory()->create();
        $user = User::factory()->create(['name' => 'テストユーザー']);

        // 翌月（8月）の勤怠データ作成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-08-10',
            'clock_in' => '2025-08-10 09:00:00',
            'clock_out' => '2025-08-10 18:00:00',
        ]);

        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list?date=2025-08-10');

        $response->assertStatus(200);
        $response->assertSee('2025-08-10');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    // 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function test_clicking_detail_button_navigates_to_attendance_detail_page()
    {
        Carbon::setTestNow('2025-07-22 10:00:00');

        // 管理者とユーザー作成
        $admin = Admin::factory()->create();
        $user = User::factory()->create(['name' => 'テストユーザー']);

        // 勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-22',
            'clock_in' => '2025-07-22 09:00:00',
            'clock_out' => '2025-07-22 18:00:00',
        ]);

        // 管理者として「詳細」リンクを開く（GETパラメータに user_id, date を渡す）

        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/show?user_id=' . $user->id . '&date=2025-07-22');

        // 遷移成功の確認
        $response->assertStatus(200);

        // ページに勤怠詳細のデータが含まれているか
        $response->assertSee('テストユーザー');
        $response->assertSee('2025-07-22');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    // 承認待ちの修正申請が全て表示されているかをテスト
    public function test_pending_attendance_applications_are_displayed_to_admin()
    {
        Carbon::setTestNow('2025-07-22 10:00:00');

        $admin = Admin::factory()->create();
        $user1 = User::factory()->create(['name' => 'ユーザーA']);
        $user2 = User::factory()->create(['name' => 'ユーザーB']);

        AttendanceApplication::factory()->create([
            'user_id' => $user1->id,
            'status' => 0,
            'reason' => '理由A',
        ]);

        AttendanceApplication::factory()->create([
            'user_id' => $user2->id,
            'status' => 0,
            'reason' => '理由B',
        ]);

        // ✅ URLを直接指定してGETリクエスト
        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get('/staff/stamp_correction_request/list?status=waiting');

        $response->assertStatus(200);
        $response->assertSee('ユーザーA');
        $response->assertSee('理由A');
        $response->assertSee('ユーザーB');
        $response->assertSee('理由B');
    }

    // 承認済み申請が全て表示されているか
    public function test_approved_attendance_applications_are_displayed_to_admin()
    {
        Carbon::setTestNow('2025-07-22 10:00:00');

        $admin = Admin::factory()->create();
        $user1 = User::factory()->create(['name' => 'ユーザーA']);
        $user2 = User::factory()->create(['name' => 'ユーザーB']);

        AttendanceApplication::factory()->create([
            'user_id' => $user1->id,
            'status' => 1, // ← 承認済み
            'reason' => '理由A',
        ]);

        AttendanceApplication::factory()->create([
            'user_id' => $user2->id,
            'status' => 1, // ← 承認済み
            'reason' => '理由B',
        ]);

        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get('/staff/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);
        $response->assertSee('ユーザーA');
        $response->assertSee('理由A');
        $response->assertSee('ユーザーB');
        $response->assertSee('理由B');
    }

    // 修正申請の内容が出勤一覧ページに表示されているか
    public function test_attendance_application_detail_is_displayed_correctly()
    {
        Carbon::setTestNow('2025-07-22 10:00:00');

        $admin = Admin::factory()->create();
        $user = User::factory()->create(['name' => 'ユーザーA']);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-22',
            'clock_in' => Carbon::parse('2025-07-22 09:00:00'),
            'clock_out' => Carbon::parse('2025-07-22 18:00:00'),
        ]);

        AttendanceApplication::factory()->create([
            'user_id' => $user->id,
            'status' => 0,
            'reason' => '詳細確認用の理由',
            'before_clock_in' => '09:00:00',
            'before_clock_out' => '18:00:00',
            'after_clock_in' => '09:30:00',
            'after_clock_out' => '18:30:00',
        ]);


        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list?date=2025-07-22');

        $response->assertStatus(200);
        $response->assertSee('ユーザーA');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    // 修正申請の承認処理が正しく行われる
    public function test_attendance_application_is_approved_and_attendance_is_updated()
    {
        Carbon::setTestNow('2025-07-22 10:00:00');

        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-20',
            'clock_in' => Carbon::parse('2025-07-20 09:00:00'),
            'clock_out' => Carbon::parse('2025-07-20 18:00:00'),
        ]);

        $application = AttendanceApplication::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 0,
            'before_clock_in' => '09:00:00',
            'before_clock_out' => '18:00:00',
            'after_clock_in' => '10:00:00',
            'after_clock_out' => '19:00:00',
        ]);

        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.attendance.update', ['date' => '2025-07-20']), [
                'user_id' => $user->id,
                'date' => '2025-07-20',
                'attendance_id' => $attendance->id,
                'clock_in' => '10:00',
                'clock_out' => '19:00',
                'reason' => '修正理由です',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('attendance_applications', [
            'id' => $application->id,
            'status' => 1,
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => Carbon::parse('2025-07-20 10:00:00'),
            'clock_out' => Carbon::parse('2025-07-20 19:00:00'),
        ]);
    }
}