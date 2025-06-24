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
    <div>
        <label>名前 {{ auth()->user()->name }}</label>
    </div>

    <!-- 日付 -->
    <div>
    <label>日付 {{ \Carbon\Carbon::parse($attendance->work_date ?? $workDate)->format('Y年n月j日') }}</label>
    </div>


    <!-- 出勤 -->
    <label>出勤・退勤</label>
    <input type="time" name="clock_in"
        value="{{ optional($attendance)->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('Y-m-d\TH:i') : '' }}">

    <span>〜</span>

    <!-- 退勤 -->
    <!-- <label>退勤</label> -->
    <input type="time" name="clock_out"
        value="{{ optional($attendance)->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('Y-m-d\TH:i') : '' }}">


    <!-- 出勤エラー -->
    @error('clock_in')
    <div style="color:red;">{{ $message }}</div>
    @enderror
    <!-- 退勤エラー -->
    @error('clock_out')
    <div style="color:red;">{{ $message }}</div>
    @enderror


    <!-- 休憩開始 -->
    @php
    $breakTimes = $attendance->breakTimes ?? collect();
    $count = $breakTimes->count();
    @endphp

    @for ($i = 0; $i < $count + 1; $i++)
        <label>
        {{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}
        </label>
        <input type="time" name="breaks[{{ $i }}][start]"
            value="{{ isset($breakTimes[$i]) ? \Carbon\Carbon::parse($breakTimes[$i]->break_start)->format('Y-m-d\TH:i') : '' }}">
        <span>〜</span>
        <input type="time" name="breaks[{{ $i }}][end]"
            value="{{ isset($breakTimes[$i]) ? \Carbon\Carbon::parse($breakTimes[$i]->break_end)->format('Y-m-d\TH:i') : '' }}">
        @endfor

        @error('break_time')
        <div style="color:red;">{{ $message }}</div>
        @enderror



        <!-- 申請理由 -->
        <label>申請理由 :</label>
        <textarea name="reason"></textarea>

        @error('reason')
        <div style="color:red;">{{ $message }}</div>
        @enderror
        <button type="submit">修正</button>
</form>


@endsection