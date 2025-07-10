<?php


namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StaffAuthController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;



class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // テスト中は Laravel のルーティングが自動では有効にならない
        // テスト用に /staff/start のルートを「手動で」登録した
        Route::middleware(['web'])->group(function () {
            Route::get('/staff/start', [\App\Http\Controllers\StaffAuthController::class, 'start']);
        });
    }

    private function createUserWithStatus(int $status)
    {
        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => $status, // 0: 勤務外, 1: 出勤中, 2: 休憩中, 3: 退勤済
            'work_date' => now()->format('Y-m-d'),
        ]);
        return $user;
    }
    // 勤務外の場合、勤怠ステータスが正しく表示されるかをテスト
    public function test_status_shows_as_not_working()
    {
        $user = $this->createUserWithStatus(0); // 勤務外

        // ($user)に赤線がつく
        // /** @var \App\Models\User $user */ これによって消えた
        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->get('/staff/start');
        $response->assertSee('勤務外');
    }
    // 出勤中の場合、勤怠ステータスが正しく表示されるかをテスト
    public function test_status_shows_as_working()
    {
        $user = $this->createUserWithStatus(1); // 出勤中

        // ($user)に赤線がつく
        // /** @var \App\Models\User $user */ これによって消えた
        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->get('/staff/start');
        $response->assertSee('出勤中');
    }
    // 休憩中の場合、勤怠ステータスが正しく表示されるかをテスト
    public function test_status_shows_as_on_break()
    {
        $user = $this->createUserWithStatus(2); // 休憩中

        // ($user)に赤線がつく
        // /** @var \App\Models\User $user */ これによって消えた
        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->get('/staff/start');
        $response->assertSee('休憩中');
    }
    // 退勤済の場合、勤怠ステータスが正しく表示されるかをテスト
    public function test_status_shows_as_finished()
    {
        $user = $this->createUserWithStatus(3); // 退勤済

        // ($user)に赤線がつく
        // /** @var \App\Models\User $user */ これによって消えた
        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->get('/staff/start');
        $response->assertSee('退勤済');
    }
}

