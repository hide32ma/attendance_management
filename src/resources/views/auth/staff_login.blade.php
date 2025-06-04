<!-- app.blade.phpを呼び出し -->
@extends('layouts.app')

@section('css')
<!-- このページで使用するcssを呼び出し -->
<link rel="stylesheet" href="{{ asset('css/staff_login.css') }}" />
@endsection






<!-- 本体 -->
@section('content')

<div>ログイン画面（一般ユーザー）</div>


<!-- authenticate = 認証する -->
<form class="authenticate center" action="/login" method="post">
    @csrf
    <h1 class="page__title">ログイン</h1>
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
    <button class="btn btn--big">ログインする</button>
    <a href="/register" class="link">会員登録はこちら</a>
</form>
    <a href="/admin/login" class="link">管理者ログインはこちら</a>




@endsection