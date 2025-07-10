<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin; // 管理者モデル（あれば）

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    // メールアドレスが未入力のときにバリデーションエラーが返るかをテスト
    public function test_email_is_required()
    {
        $response = $this->post('admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }
    // パスワードが未入力のときにバリデーションエラーが返るかをテスト
    public function test_password_is_required()
    {
        $response = $this->post('admin/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
    }
    // 登録内容と一致しない場合、バリデーションメッセージが表示されるかをテスト
    public function test_admin_login_fails_with_invalid_credentials()
    {
        // ダミーユーザーを登録（正しいログイン情報）
        // ここに記述されているemailとpasswordは、なんでも良い!
        // ただし、このあとログインに使う値と「一致させる／わざと間違える」ことが目的なので、そこだけちゃんと意識して決めること！
        // パスワードは 必ず bcrypt() でハッシュ化
        // Laravelでは、DBに保存するパスワードはハッシュ化されています。
        // だからこのようにしないとログインできません：
        Admin::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 間違ったメールアドレスとパスワードでログインを試みる
        $response = $this->post('admin/login', [
            'email' => 'testing@example.com',
            'password' => 'wrongpassword',
        ]);

        // ログイン失敗後、セッションにエラーが格納されることを確認
        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません', // 実際のエラーメッセージに合わせて調整
        ]);

        // 認証されていないことを確認
        $this->assertGuest();
    }
}
