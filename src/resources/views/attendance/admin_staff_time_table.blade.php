<!-- app.blade.phpを呼び出し -->
@extends('layouts.app')

@section('css')
<!-- このページで使用するcssを呼び出し -->
<link rel="stylesheet" href="{{ asset('css/admin_staff_list.css') }}" />
@endsection

<!-- 本体 -->
@section('content')


<div class="container">
    <h2>{{ $user->name }}さんの勤怠</h2>


    <div class="d-flex">
        <a href="{{ route('admin.attendance.staff', $user->id) }}?month={{ $date->copy()->subMonth()->format('Y-m') }}">← 前月</a>
        <h4>{{ $date->format('Y年m月') }}</h4>
        <a href="{{ route('admin.attendance.staff', $user->id) }}?month={{ $date->copy()->addMonth()->format('Y-m') }}">次月 ➝</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dates as $day)
                @php
                    $dateStr = $day->format('Y-m-d');
                    $attendance = $attendances[$dateStr] ?? null;

                    $clockIn = isset($attendance->clock_in) ? \Carbon\Carbon::parse($attendance->clock_in) : null;
                    $clockOut = isset($attendance->clock_out) ? \Carbon\Carbon::parse($attendance->clock_out) : null;

                    $breakMinutes = $attendance && $attendance->breakTimes
                        ? $attendance->breakTimes->sum(function ($break) {
                            return \Carbon\Carbon::parse($break->break_end)->diffInMinutes(\Carbon\Carbon::parse($break->break_start));
                        }) : null;

                    $workedMinutes = ($clockIn && $clockOut) ? $clockIn->diffInMinutes($clockOut) - $breakMinutes : null;
                @endphp

                <tr>
                    <!-- 日付 -->
                    <td>{{ $day->locale('ja')->isoFormat('MM/DD(ddd)') }}</td>
                    <!-- 出勤 -->
                    <td>{{ $clockIn ? $clockIn->format('H:i') : '' }}</td>
                    <!-- 退勤 -->
                    <td>{{ $clockOut ? $clockOut->format('H:i') : '' }}</td>
                    <!-- 休憩 -->
                    <td>
                        @if (!is_null($attendance) && $attendance->breakTimes->isNotEmpty())
                            {{ sprintf('%d:%02d', floor($breakMinutes / 60), $breakMinutes % 60) }}
                        @endif
                    </td>
                    <!-- 勤務時間合計 -->
                    <td>
                        {{ $workedMinutes !== null ? sprintf('%d:%02d', floor($workedMinutes / 60), $workedMinutes % 60) : '' }}
                    </td>
                    <td><a href="{{ route('admin.attendance.show', [
                        'user_id' => $user->id,
                        'date' => $day->format('Y-m-d')
                    ]) }}">
                        詳細
                    </a></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <form method="POST" action="">
        @csrf
        <button type="submit" class="btn btn-primary">CSV出力</button>
    </form>
</div>


@endsection