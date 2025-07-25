<!-- app.blade.phpを呼び出し -->
@extends('layouts.app')

@section('css')
<!-- このページで使用するcssを呼び出し -->
<link rel="stylesheet" href="{{ asset('css/staff_start.css') }}" />
@endsection

<!-- 本体 -->
@section('content')





<div>
    @foreach ($attendances as $attendance)
    <tr>
        <td>
            <!-- 勤務ステータスが表示される -->
            <div class="user_status">{{ $attendance->status_label }}</div>
        </td>

        <td>
            <!-- ログインユーザー名を表示 -->
            <div class="user_name">ログインユーザー名：{{ $attendance->user->name }}</div>
        </td>
    </tr>

    <!-- 出勤ボタン -->
    @if ($attendance && $attendance->status === \App\Models\Attendance::STATUS_OFF)
    <form method="post" action="/attendance/start">
        @csrf
        <button class="attendance_btn" type="submit">出勤</button>
    </form>
    <!-- ステータスが出勤中のとき：休憩入＆退勤 -->
    @elseif ($attendance && $attendance->status === \App\Models\Attendance::STATUS_WORKING)
    <form method="post" action="/attendance/break-in">
        @csrf
        <button class="break_in_btn" type="submit">休憩入</button>
    </form>

    <form method="post" action="/attendance/end">
        @csrf
        <button class="leaving_work_btn" type="submit">退勤</button>
    </form>

    <!-- ステータスが休憩中のとき：休憩戻 -->
    @elseif ($attendance && $attendance->status === \App\Models\Attendance::STATUS_BREAK)
    <form method="post" action="/attendance/break-out">
        @csrf
        <button class="break_out_btn" type="submit">休憩戻</button>
    </form>
    @endif


    @endforeach

    <!-- フラッシュメッセージ -->
    <!-- コントローラーから読み取っている -->
    @if (session('success'))
    <div class="alert_success">
        {{ session('success') }}
    </div>
    @endif


    <!-- 現在の時間が表示される -->
    <!-- Carbonで読み取っている -->
    <div class="date_time">{!! nl2br(e($nowDateTime)) !!}</div>




</div>

@endsection