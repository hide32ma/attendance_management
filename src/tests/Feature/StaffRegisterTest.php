<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StaffRegisterTest extends TestCase
{
    use RefreshDatabase;

    // 名前が未入力のときにバリデーションエラーが返るかをテスト
    public function test_name_is_required()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['name']);
    }
    // メールアドレスが未入力のときにバリデーションエラーが返るかをテスト
    public function test_email_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }
    // パスワードが未入力のときにバリデーションエラーが返るかをテスト
    public function test_password_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
    }
    // パスワードとパスワード確認が一致しない時にバリデーションエラーが返るかをテスト
    public function test_password_confirmation_must_match()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'wrongpassword',
            // wrongpassword ここの文字はパスワードと一緒じゃなければなんでも良い
        ]);

        $response->assertSessionHasErrors(['password']);
    }
    // フォームに内容が入力されていた場合、データが正常にDBに保存されるかをテスト
    public function test_user_can_register_successfully()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 登録後にリダイレクトされることを確認（適宜パス変更）
        $response->assertRedirect('/'); // '/home' or '/dashboard' or '/login' 等

        // データベースに登録されていることを確認
        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);
    }
}
