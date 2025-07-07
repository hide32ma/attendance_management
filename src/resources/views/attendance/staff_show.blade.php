<!-- app.blade.phpを呼び出し -->
@extends('layouts.app')

@section('css')
<!-- このページで使用するcssを呼び出し -->
<link rel="stylesheet" href="{{ asset('css/staff_show.css') }}" />
@endsection

<!-- 本体 -->
@section('content')


<!-- 管理者(admin)ログイン中 -->
@if (Auth::guard('admin')->check())

<form action="{{ route('admin.attendance.update', ['date' => $workDate]) }}" method="POST">

    @csrf

    @if(isset($user))
    <input type="hidden" name="user_id" value="{{ $user->id }}">
    @endif

    @if(isset($attendance))
    <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
    @endif

    <h2>勤怠詳細（管理者）</h2>

    @if (auth('admin')->check())

    @if ($user)
    <h2>{{ $user->name }} さんの勤怠情報</h2>
    @else
    <h2>ユーザー情報が取得できませんでした。</h2>
    @endif

    <!-- 名前 -->
    <div>
        <label>名前：</label>{{ $user->name }}
    </div>
    <!-- 日付 -->
    <div>
        <label>日付：</label>{{ \Carbon\Carbon::parse($attendance->work_date ?? $workDate)->format('Y年n月j日') }}
    </div>

    <!-- 出勤 -->
    <label>出勤・退勤</label>
    <input type="time" name="clock_in"
        value="{{ optional($attendance)->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}">

    <span>〜</span>

    <!-- 退勤 -->
    <!-- <label>退勤</label> -->
    <input type="time" name="clock_out"
        value="{{ optional($attendance)->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}">


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

        {{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}

        <input type="time" name="breaks[{{ $i }}][start]"
        value="{{ isset($breakTimes[$i]) ? \Carbon\Carbon::parse($breakTimes[$i]->break_start)->format('H:i') : '' }}">
        <span>〜</span>
        <input type="time" name="breaks[{{ $i }}][end]"
            value="{{ isset($breakTimes[$i]) ? \Carbon\Carbon::parse($breakTimes[$i]->break_end)->format('H:i') : '' }}">
        @endfor

        @error('break_time')
        <div style="color:red;">{{ $message }}</div>
        @enderror



        <!-- 申請理由 -->
        <label>申請理由 :</label>
        <textarea name="reason">{{ old('reason', $attendance->reason ?? '') }}</textarea>


        @error('reason')
        <div style="color:red;">{{ $message }}</div>
        @enderror

        <!-- <button type="submit">修正</button> -->
        @if (request()->query('from') === 'approval')
        @if ($application && $application->status === 1)
        <button disabled>承認済み</button>
        @else
        <form method="POST" action="{{ route('admin.attendance.approve') }}">
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}">
            <input type="hidden" name="date" value="{{ $attendance->work_date }}">
            <button type="submit">承認</button>
        </form>
        @endif
        @else
        <button type="submit">修正</button>
        @endif
</form>

@if (session('message'))
<div style="color: green; margin: 10px 0;">
    {{ session('message') }}
</div>
@endif

@error('attendance')
<p class="text-danger" style="color: red;">{{ $message }}</p>
@enderror

<!-- @if (session('message')) -->
<!-- <div style="color: green;">{{ session('message') }}</div> -->
<!-- @endif -->


@endif


<!-- 一般ユーザー(staff)ログイン中 -->
@elseif (Auth::guard('web')->check())

<h2>勤怠詳細（一般ユーザー）</h2>

@if (!$application)
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
        value="{{ optional($attendance)->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}">

    <span>〜</span>

    <!-- 退勤 -->
    <!-- <label>退勤</label> -->
    <input type="time" name="clock_out"
        value="{{ optional($attendance)->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}">


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

        {{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}

        <input type="time" name="breaks[{{ $i }}][start]"
        value="{{ isset($breakTimes[$i]) ? \Carbon\Carbon::parse($breakTimes[$i]->break_start)->format('H:i') : '' }}">
        <span>〜</span>
        <input type="time" name="breaks[{{ $i }}][end]"
            value="{{ isset($breakTimes[$i]) ? \Carbon\Carbon::parse($breakTimes[$i]->break_end)->format('H:i') : '' }}">
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

@if (session('message'))
<div style="color: green; margin: 10px 0;">
    {{ session('message') }}
</div>
@endif

@error('attendance')
<p class="text-danger" style="color: red;">{{ $message }}</p>
@enderror

@endif

<!-- 修正申請が存在する場合 -->


@if ($application)

<!-- 名前 -->
<div>
    <label>名前 {{ auth()->user()->name }}</label>
</div>

<!-- 日付 -->
<div>
    <label>日付 {{ \Carbon\Carbon::parse($attendance->work_date ?? $workDate)->format('Y年n月j日') }}</label>
</div>


<div>出勤・退勤 {{ \Carbon\Carbon::parse($application->after_clock_in)->format('H:i') }}
    〜
    {{ \Carbon\Carbon::parse($application->after_clock_out)->format('H:i') }}
</div>

<div>休憩
    @foreach (json_decode($application->after_breaks_json, true) as $break)
    {{ $break['start'] }}〜{{ $break['end'] }}
    @endforeach
</div>
<div>備考{{ $application->reason }}</div>

<div class="text-danger">※承認待ちのため修正はできません。</div>

@endif


@endif



@endsection