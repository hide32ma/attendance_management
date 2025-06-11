<?php

namespace App\Providers;


use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;

use Illuminate\Http\Request;


use Illuminate\Cache\RateLimiting\Limit;

// 追記しました
use Illuminate\Support\Facades\Auth;
// 追記しました
use Illuminate\Support\Facades\Hash;
// StaffLoginRequestを読み込む
use App\Http\Requests\StaffLoginRequest;
// 追記しました
use App\Models\Admin;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\LoginResponse;

// 追記
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        // 新規ユーザの登録処理
        Fortify::createUsersUsing(CreateNewUser::class);

        // COACHTECH Laravel 3-5 ユーザー認証について学ぼう 今回使わない機能に関しては消去との事なので以下の機能は今は消去しておく
        // Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        // Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        // Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // RateLimiter::for('login', function (Request $request) {
        // $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

        // return Limit::perMinute(5)->by($throttleKey);
        // });

        // RateLimiter::for('two-factor', function (Request $request) {
        // return Limit::perMinute(5)->by($request->session()->get('login.id'));
        // });


        // GETメソッドで/registerにアクセスしたときに表示するviewファイル
        Fortify::registerView(function () {
                return view('auth.staff_register');
        });

        // GETメソッドで/loginにアクセスしたときに表示するviewファイル
        Fortify::loginView(function () {
                return view('auth.staff_login');
        });

        // login処理の実行回数を1分あたり10回までに制限
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });

        //デフォルトのログイン機能にあるフォームリクエストを自作のものに代替するため、サービスコンテナにバインド
        // (バリデーションメッセージがこれにより変わります)
        app()->bind(FortifyLoginRequest::class, StaffLoginRequest::class);

        // admin(管理者)もFortifyを使用できるようにする
        Fortify::authenticateUsing(function (Request $request) {
            if ($request->is('admin/login')) {
                // 管理者ログイン処理
                $admin = Admin::where('email', $request->email)->first();
                if ($admin && Hash::check($request->password, $admin->password)) {
                    Auth::guard('admin')->login($admin);
                    return $admin;
                }
                return null;
            }

            // 一般ユーザー認証（手動）
            $user = \App\Models\User::where('email', $request->email)->first();
            if ($user && Hash::check($request->password, $user->password)) {
                Auth::login($user);
                return $user;
            }
            // 通常のユーザーログイン（Fortifyが処理）
            return null;
        });

        // ログイン後のリダイレクト制御
        app()->singleton(\Laravel\Fortify\Contracts\LoginResponse::class, function () {
            return new class implements \Laravel\Fortify\Contracts\LoginResponse {
                public function toResponse($request)
                {
                    if ($request->is('admin/login')) {
                        // 管理者の場合のリダイレクト先
                        return redirect('/admin/attendance/list');
                    }
                    // 一般ユーザーの場合のリダイレクト先
                    return redirect('/');
                }
            };
        });


    }
}