<!-- app.blade.phpを呼び出し -->
@extends('layouts.app')

@section('css')
<!-- このページで使用するcssを呼び出し -->
<link rel="stylesheet" href="{{ asset('css/admin_login.css') }}" />
@endsection

<!-- 本体 -->
@section('content')
<!-- authenticate = 認証する -->
<form class="authenticate__center" action="/admin/login" method="post">
    @csrf
    <h1 class="page__title">管理者ログイン</h1>
    <label class="entry__mail__address" for="mail">メールアドレス</label>
    <input class="input__mail__address" name="email" id="mail" type="email" value="{{ old('email') }}">
    <div class="form__address__error">
        @error('email')
        {{ $message }}
        @enderror
    </div>
    <label class="entry__password" for="password">パスワード</label>
    <input class="input__password" name="password" id="password" type="password">
    <div class="form__password__error">
        @error('password')
        {{ $message }}
        @enderror
    </div>
    <div class="auth-login">
        <button class="btn--big">管理者ログインする</button>
    </div>
</form>

<a href="/login" class="login__link">一般ログインはこちら</a>


@endsection