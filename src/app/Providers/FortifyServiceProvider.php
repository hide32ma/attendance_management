<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
// LoginRequestを読み込む
use App\Http\Requests\LoginRequest;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

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
                return view('auth.register');
        });

        // GETメソッドで/loginにアクセスしたときに表示するviewファイル
        Fortify::loginView(function () {
                return view('auth.login');
        });

        // login処理の実行回数を1分あたり10回までに制限
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });

        //デフォルトのログイン機能にあるフォームリクエストを自作のものに代替するため、サービスコンテナにバインド
        app()->bind(FortifyLoginRequest::class, LoginRequest::class);
    }

}