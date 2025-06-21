<!-- app.blade.phpを呼び出し -->
@extends('layouts.app')

@section('css')
<!-- このページで使用するcssを呼び出し -->
<link rel="stylesheet" href="{{ asset('css/admin_index.css') }}" />
@endsection






<!-- 本体 -->
@section('content')

<div>勤務一覧画面（管理者）</div>
<div>ログイン時のみ表示</div>

@endsection