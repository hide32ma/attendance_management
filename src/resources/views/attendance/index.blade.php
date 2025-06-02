<!-- app.blade.phpを呼び出し -->
@extends('layouts.app')

@section('css')
<!-- このページで使用するcssを呼び出し -->
<link rel="stylesheet" href="{{ asset('css/index.css') }}" />
@endsection






<!-- 本体 -->
@section('content')

<div>勤務一覧画面（一般ユーザー）</div>

@endsection