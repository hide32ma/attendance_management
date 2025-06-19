<!-- app.blade.phpを呼び出し -->
@extends('layouts.app')

@section('css')
<!-- このページで使用するcssを呼び出し -->
<link rel="stylesheet" href="{{ asset('css/staff_start.css') }}" />
@endsection






<!-- 本体 -->
@section('content')

<div>勤怠登録画面（一般ユーザー）</div>



<div>
    <!-- 勤務ステータスが表示される -->
    @foreach ($attendances as $attendance)
    <tr>
        <td>{{ $attendance->user->name }}</td>
        <td>{{ $attendance->status_label }}</td>
    </tr>

    <!-- 出勤ボタン -->
    @if ($attendance && $attendance->status === \App\Models\Attendance::STATUS_OFF)
    <form method="post" action="/attendance/start">
        @csrf
        <button type="submit">出勤</button>
    </form>
    <!-- ステータスが出勤中のとき：休憩入＆退勤 -->
    @elseif ($attendance && $attendance->status === \App\Models\Attendance::STATUS_WORKING)
    <form method="post" action="/attendance/break-in">
        @csrf
        <button type="submit">休憩入</button>
    </form>

    <form method="post" action="/attendance/end">
        @csrf
        <button type="submit">退勤</button>
    </form>

    <!-- ステータスが休憩中のとき：休憩戻 -->
    @elseif ($attendance && $attendance->status === \App\Models\Attendance::STATUS_BREAK)
    <form method="post" action="/attendance/break-out">
        @csrf
        <button type="submit">休憩戻</button>
    </form>
    @endif


    @endforeach

    <!-- フラッシュメッセージ -->
    <!-- コントローラーから読み取っている -->
    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif


    <!-- 現在の時間が表示される -->
    <!-- Carbonで読み取っている -->
    <div>{!! nl2br(e($nowDateTime)) !!}</div>




</div>

@endsection