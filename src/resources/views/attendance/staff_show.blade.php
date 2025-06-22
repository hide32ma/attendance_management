<!-- app.blade.phpを呼び出し -->
@extends('layouts.app')

@section('css')
<!-- このページで使用するcssを呼び出し -->
<link rel="stylesheet" href="{{ asset('css/staff_show.css') }}" />
@endsection






<!-- 本体 -->
@section('content')

<h2>勤務詳細（一般ユーザー）</h2>





<form action="{{ route('staff.attendance.update', ['date' => $workDate]) }}" method="POST">
    @csrf

    <!-- 名前 -->
    <label>名前</label>
    {{ auth()->user()->name }}

    <!-- 日付 -->
    <label>日付</label>
    {{ $attendance->work_date ?? $workDate }}


    <!-- 出勤 -->
    <label>出勤</label>
    <input type="datetime-local" name="clock_in"
        value="{{ optional($attendance)->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('Y-m-d\TH:i') : '' }}">

    <span>〜</span>

    <!-- 退勤 -->
    <label>退勤</label>
    <input type="datetime-local" name="clock_out"
        value="{{ optional($attendance)->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('Y-m-d\TH:i') : '' }}">

    <!-- 休憩開始 -->
    <label>休憩</label>
    <input type="datetime-local" name="breaks[0][start]"
        value="{{ optional($attendance->breakTimes[0] ?? null)->break_start
                    ? \Carbon\Carbon::parse($attendance->breakTimes[0]->break_start)->format('Y-m-d\TH:i') : '' }}">

    <span>〜</span>

    <!-- 休憩終了 -->
    <!-- <label>休憩</label> -->
    <input type="datetime-local" name="breaks[0][end]"
        value="{{ optional($attendance->breakTimes[0] ?? null)->break_end
                    ? \Carbon\Carbon::parse($attendance->breakTimes[0]->break_end)->format('Y-m-d\TH:i') : '' }}">

    <!-- 申請理由 -->
    <label>申請理由 :</label>
    <textarea name="reason"></textarea>

    <button type="submit">修正</button>
</form>


@endsection