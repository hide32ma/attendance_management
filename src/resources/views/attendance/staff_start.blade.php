<!-- app.blade.phpを呼び出し -->
@extends('layouts.app')

@section('css')
<!-- このページで使用するcssを呼び出し -->
<link rel="stylesheet" href="{{ asset('css/start.css') }}" />
@endsection






<!-- 本体 -->
@section('content')

<div>勤怠登録画面（一般ユーザー）</div>
<div>ログイン時のみ表示</div>


<div>
    <div>{!! nl2br(e($nowDateTime)) !!}</div>
</div>

@endsection