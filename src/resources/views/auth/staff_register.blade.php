<!-- app.blade.phpを呼び出し -->
@extends('layouts.app')

@section('css')
<!-- このページで使用するcssを呼び出し -->
<link rel="stylesheet" href="{{ asset('css/staff_register.css') }}" />
@endsection

<!-- 本体 -->
@section('content')

<!-- authenticate = 認証する -->
<!-- Fortify -->
<form class="authenticate__center" action="/register" method="post">
    @csrf
    <h1 class="page__title">会員登録</h1>
    <label class="entry__user__name" for="name">名前</label>
    <input class="input_user__name" name="name" id="name" type="text" value="{{ old('name') }}">
    <div class="form__name__error">
        @error('name')
        {{ $message }}
        @enderror
    </div>
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
    <label class="entry__password__confirm" for="password_confirm">パスワード確認</label>
    <input class="input__password__confirm" name="password_confirmation" id="password_confirm" type="password">

    <div class="auth-registration">
        <button class="btn--big">登録する</button>
    </div>
        <a href="/login" class="login__link">ログインはこちら</a>
</form>

@endsection