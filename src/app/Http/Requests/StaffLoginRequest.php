<?php

namespace App\Http\Requests;

// Fortifyが内部的に用意しているログインリクエスト
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

//デフォルトのログイン機能には上述のFortifyLoginRequestクラスが引数として渡されているため、異なるクラスを呼び出すとエラーが出力される。
//そのため、デフォルトにある上述のFortifyLoginRequestクラスを継承する必要がある
class StaffLoginRequest extends FortifyLoginRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => ['required', 'string','email', 'max:255'],
            'password' => ['required','string', 'min:8']
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => 'メール形式で入力してください',
            'email.confirmed' => 'ログイン情報が登録されていません',
            'password.required' => 'パスワードを入力してください',
            'password.min' => 'パスワードは8文字以上で入力してください',
        ];
    }
}
