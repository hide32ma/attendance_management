<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use Illuminate\Support\Carbon;
use Tests\TestCase;
use App\Models\User;



class DateTimeTest extends TestCase
{
    use RefreshDatabase;

    // 現在の日時情報がUIと同じ形式で出力されているかをテスト
    public function test_now_datetime_is_displayed_on_staff_start_page()
    {
        // 認証ユーザーを作成してログイン
        $user = User::factory()->create();
        // ($user)に赤線がつく
        // /** @var \App\Models\User $user */ これによって消えた
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        // 表示される形式に合わせて文字列を組み立て
        Carbon::setLocale('ja'); // 曜日を日本語にする
        $expectedDatetime = Carbon::now()->translatedFormat('Y年n月j日（D）') . "<br />\n" . Carbon::now()->format('H:i');

        // / にアクセス
        $response = $this->get('/');

        // Bladeでnl2brされても、文字列がHTMLに含まれていればOK
        $response->assertSee($expectedDatetime, false); // 第二引数falseでHTML無視
    }
}
