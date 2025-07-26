<!-- app.blade.phpを呼び出し -->
@extends('layouts.app')

@section('css')
<!-- このページで使用するCSS -->
<link rel="stylesheet" href="{{ asset('css/admin_staff_time_table.css') }}">
@endsection

@section('content')
<div class="admin-attendance-container">
    <h2 class="admin-attendance-title">{{ $user->name }}さんの勤怠</h2>

    <div class="admin-attendance-nav">
        <a href="{{ route('admin.attendance.staff', $user->id) }}?month={{ $date->copy()->subMonth()->format('Y-m') }}">← 前月</a>
        <h4>{{ $date->format('Y年m月') }}</h4>
        <a href="{{ route('admin.attendance.staff', $user->id) }}?month={{ $date->copy()->addMonth()->format('Y-m') }}">次月 →</a>
    </div>

    <table class="admin-attendance-table">
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

            $workedMinutes = ($clockIn && $clockOut)
            ? $clockIn->diffInMinutes($clockOut) - $breakMinutes
            : null;
            @endphp
            <tr>
                <td>{{ $day->locale('ja')->isoFormat('MM/DD(ddd)') }}</td>
                <td>{{ $clockIn ? $clockIn->format('H:i') : '' }}</td>
                <td>{{ $clockOut ? $clockOut->format('H:i') : '' }}</td>
                <td>{{ $breakMinutes !== null ? sprintf('%d:%02d', floor($breakMinutes / 60), $breakMinutes % 60) : '' }}</td>
                <td>{{ $workedMinutes !== null ? sprintf('%d:%02d', floor($workedMinutes / 60), $workedMinutes % 60) : '' }}</td>
                <td>
                    <a href="{{ route('admin.attendance.show', [
                            'user_id' => $user->id,
                            'date' => $day->format('Y-m-d')
                        ]) }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <form method="POST" action="">
        @csrf
        <button type="submit" class="admin-attendance-export-btn">CSV出力</button>
    </form>
</div>
@endsection