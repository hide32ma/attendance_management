<!-- app.blade.phpを呼び出し -->
@extends('layouts.app')

@section('css')
<!-- このページで使用するcssを呼び出し -->
<link rel="stylesheet" href="{{ asset('css/register.css') }}" />
@endsection






<!-- 本体 -->
@section('content')

<div>会員登録画面（一般ユーザー）</div>


<!-- authenticate = 認証する -->
<form class="authenticate center" action="/register" method="post">
    @csrf
    <h1 class="page__title">会員登録</h1>
    <label class="entry__name" for="name">名前</label>
    <input class="input" name="name" id="name" type="text" value="{{ old('name') }}">
    <div class="form__error">
        @error('name')
        {{ $message }}
        @enderror
    </div>
    <label class="entry__name" for="mail">メールアドレス</label>
    <input class="input" name="email" id="mail" type="email" value="{{ old('email') }}">
    <div class="form__error">
        @error('email')
        {{ $message }}
        @enderror
    </div>
    <label class="entry__name" for="password">パスワード</label>
    <input class="input" name="password" id="password" type="password">
    <div class="form__error">
        @error('password')
        {{ $message }}
        @enderror
    </div>
    <label class="entry__name" for="password_confirm">パスワード確認</label>
    <input class="input" name="password_confirmation" id="password_confirm" type="password">
    <button class="btn btn--big">登録する</button>
    <a href="/login" class="link">ログインはこちら</a>
</form>

@endsection