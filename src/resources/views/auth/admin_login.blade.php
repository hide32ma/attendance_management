<!-- app.blade.phpを呼び出し -->
@extends('layouts.app')

@section('css')
<!-- このページで使用するcssを呼び出し -->
<link rel="stylesheet" href="{{ asset('css/admin_login.css') }}" />
@endsection






<!-- 本体 -->
@section('content')


<div>管理者ログイン画面</div>

<a href="/login" class="link">スタッフログインはこちら</a>



@endsection