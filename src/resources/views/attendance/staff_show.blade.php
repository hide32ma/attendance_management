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

<form action="{{ route('admin.attendance.update', ['date' => $workDate]) }}" method="POST" class="admin-attendance__form">
    @csrf

    @if(isset($user))
    <input type="hidden" name="user_id" value="{{ $user->id }}">
    @endif

    @if(isset($attendance))
    <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
    @endif

    <h2 class="admin-attendance__title">勤怠詳細</h2>

    <div class="admin-attendance__card">

        <div class="admin-attendance__row">
            <label class="admin-attendance__label">名前</label>
            <span class="admin-attendance__value">{{ $user->name }}</span>
        </div>

        <div class="admin-attendance__row">
            <label class="admin-attendance__label">日付</label>
            <span class="admin-attendance__value">{{ \Carbon\Carbon::parse($attendance->work_date ?? $workDate)->format('Y年n月j日') }}</span>
        </div>

        <div class="admin-attendance__row">
            <label class="admin-attendance__label">出勤・退勤</label>
            <input type="time" name="clock_in" class="admin-attendance__input"
                value="{{ optional($attendance)->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}">
            <span>〜</span>
            <input type="time" name="clock_out" class="admin-attendance__input"
                value="{{ optional($attendance)->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}">
        </div>

        @error('clock_in')
        <div class="admin-attendance__error">{{ $message }}</div>
        @enderror
        @error('clock_out')
        <div class="admin-attendance__error">{{ $message }}</div>
        @enderror

        @php
        $breakTimes = $attendance->breakTimes ?? collect();
        $count = $breakTimes->count();
        @endphp

        @for ($i = 0; $i < $count + 1; $i++)
            <div class="admin-attendance__row">
            <label class="admin-attendance__label">{{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}</label>
            <input type="time" name="breaks[{{ $i }}][start]" class="admin-attendance__input"
                value="{{ isset($breakTimes[$i]) ? \Carbon\Carbon::parse($breakTimes[$i]->break_start)->format('H:i') : '' }}">
            <span>〜</span>
            <input type="time" name="breaks[{{ $i }}][end]" class="admin-attendance__input"
                value="{{ isset($breakTimes[$i]) ? \Carbon\Carbon::parse($breakTimes[$i]->break_end)->format('H:i') : '' }}">
    </div>
    @endfor

    @error('break_time')
    <div class="admin-attendance__error">{{ $message }}</div>
    @enderror

    <div class="admin-attendance__row">
        <label class="admin-attendance__label">備考</label>
        <textarea name="reason" class="admin-attendance__textarea">{{ old('reason', $attendance->reason ?? '') }}</textarea>
    </div>

    @error('reason')
    <div class="admin-attendance__error">{{ $message }}</div>
    @enderror

    <div class="admin-attendance__row admin-attendance__button-row">
        <button type="submit" class="admin-attendance__button">修正</button>
    </div>
    </div>
</form>

@if (session('message'))
<div class="admin-attendance__message">{{ session('message') }}</div>
@endif

@error('attendance')
<p class="admin-attendance__error">{{ $message }}</p>
@enderror


<!-- 一般ユーザー(staff)ログイン中 -->
@elseif (Auth::guard('web')->check())

<h2 class="attendance-form__title">勤怠詳細</h2>

@if (!$application)
<form action="{{ route('staff.attendance.update', ['date' => $workDate]) }}" method="POST" class="attendance-form">
    @csrf

    <!-- 名前 -->
    <div class="attendance-form__row">
        <label class="attendance-form__label">名前：</label>
        <span class="attendance-form__value">{{ auth()->user()->name }}</span>
    </div>

    <!-- 日付 -->
    <div class="attendance-form__row">
        <label class="attendance-form__label">日付：</label>
        <span class="attendance-form__value">
            {{ \Carbon\Carbon::parse($attendance->work_date ?? $workDate)->format('Y年n月j日') }}
        </span>
    </div>

    <!-- 出勤・退勤 -->
    <div class="attendance-form__row">
        <label class="attendance-form__label">出勤・退勤</label>
        <input type="time" name="clock_in" class="attendance-form__input"
            value="{{ optional($attendance)->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}">
        <span>〜</span>
        <input type="time" name="clock_out" class="attendance-form__input"
            value="{{ optional($attendance)->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}">
    </div>

    @error('clock_in')
    <div class="attendance-form__error">{{ $message }}</div>
    @enderror
    @error('clock_out')
    <div class="attendance-form__error">{{ $message }}</div>
    @enderror

    <!-- 休憩 -->
    @php
    $breakTimes = $attendance->breakTimes ?? collect();
    $count = $breakTimes->count();
    @endphp

    @for ($i = 0; $i < $count + 1; $i++)
        <div class="attendance-form__row">
        <label class="attendance-form__label">{{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}</label>
        <input type="time" name="breaks[{{ $i }}][start]" class="attendance-form__input"
            value="{{ isset($breakTimes[$i]) ? \Carbon\Carbon::parse($breakTimes[$i]->break_start)->format('H:i') : '' }}">
        <span>〜</span>
        <input type="time" name="breaks[{{ $i }}][end]" class="attendance-form__input"
            value="{{ isset($breakTimes[$i]) ? \Carbon\Carbon::parse($breakTimes[$i]->break_end)->format('H:i') : '' }}">
        </div>
        @endfor

        @error('break_time')
        <div class="attendance-form__error">{{ $message }}</div>
        @enderror

        <!-- 備考 -->
        <div class="attendance-form__row">
            <label class="attendance-form__label">備考：</label>
            <textarea name="reason" class="attendance-form__textarea"></textarea>
        </div>

        @error('reason')
        <div class="attendance-form__error">{{ $message }}</div>
        @enderror

        <div class="attendance-form__row" style="justify-content: flex-end;">
            <button type="submit" class="attendance-form__button">修正</button>
        </div>
</form>

@if (session('message'))
<div class="attendance-form__message">{{ session('message') }}</div>
@endif

@error('attendance')
<p class="attendance-form__error">{{ $message }}</p>
@enderror

@endif


<!-- 修正申請が存在する場合 -->

@if ($application)

<div class="readonly-attendance__card">

    <div class="readonly-attendance__row">
        <label class="readonly-attendance__label">名前</label>
        <span class="readonly-attendance__value">{{ auth()->user()->name }}</span>
    </div>

    <div class="readonly-attendance__row">
        <label class="readonly-attendance__label">日付</label>
        <span class="readonly-attendance__value">{{ \Carbon\Carbon::parse($attendance->work_date ?? $workDate)->format('Y年n月j日') }}</span>
    </div>

    <div class="readonly-attendance__row">
        <label class="readonly-attendance__label">出勤・退勤</label>
        <span class="readonly-attendance__value">
            @if ($application->before_clock_in && $application->before_clock_out)
            {{ \Carbon\Carbon::parse($application->before_clock_in)->format('H:i') }} 〜 {{ \Carbon\Carbon::parse($application->before_clock_out)->format('H:i') }}
            @else
            〜〜
            @endif
        </span>
    </div>

    <div class="readonly-attendance__row">
        <label class="readonly-attendance__label">休憩</label>
        <span class="readonly-attendance__value">
            @if ($application->before_breaks_json)
            @foreach (json_decode($application->before_breaks_json, true) as $break)
            {{ \Carbon\Carbon::parse($break['start'])->format('H:i') }}〜{{ \Carbon\Carbon::parse($break['end'])->format('H:i') }}<br>
            @endforeach
            @else
            -
            @endif
        </span>
    </div>

    <div class="readonly-attendance__row">
        <label class="readonly-attendance__label">備考</label>
        <span class="readonly-attendance__value">{{ $application->reason }}</span>
    </div>

</div>

<div class="readonly-attendance__notice">※承認待ちのため修正はできません。</div>

@endif



@endif



@endsection