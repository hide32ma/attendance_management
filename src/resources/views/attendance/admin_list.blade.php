<!-- app.blade.phpを呼び出し -->
@extends('layouts.app')

@section('css')
<!-- このページで使用するcssを呼び出し -->
<link rel="stylesheet" href="{{ asset('css/admin_index.css') }}" />
@endsection






<!-- 本体 -->
@section('content')

<h2>{{ \Carbon\Carbon::parse($workDate)->format('Y年n月j日') }}の勤怠</h2>

<div>
    <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}">← 前日</a>

    @php
        $baseUrl = route('admin.attendance.list');
    @endphp

    <input type="date" value="{{ $workDate }}"
        onchange="location.href='{{ $baseUrl }}?date=' + this.value">

    <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}">翌日 →</a>
</div>


<table>
    <thead>
        <tr>
            <th>名前</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
        @php
            $attendance = $attendances->firstWhere('user_id', $user->id);
            $targetDateCarbon = \Carbon\Carbon::parse($targetDate);
        @endphp
        <tr>
            <td>{{ $user->name }}</td>
            <td>
                {{ $attendance && $attendance->clock_in
                        ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
                        : '---' }}
            </td>
            <td>
                {{ $attendance && $attendance->clock_out
                        ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
                        : '---' }}
            </td>
            <td>
                @if ($attendance && $attendance->breakTimes->isNotEmpty())
                @php
                $breakMinutes = $attendance->breakTimes->sum(function ($break) {
                return \Carbon\Carbon::parse($break->break_end)->diffInMinutes(\Carbon\Carbon::parse($break->break_start));
                });
                echo floor($breakMinutes / 60) . ':' . str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT);
                @endphp
                @else
                ---
                @endif
            </td>
            <td>
                @if ($attendance && $attendance->clock_in && $attendance->clock_out)
                @php
                $workedMinutes = \Carbon\Carbon::parse($attendance->clock_in)->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_out));
                $breakMinutes = $attendance->breakTimes->sum(function ($break) {
                return \Carbon\Carbon::parse($break->break_end)->diffInMinutes(\Carbon\Carbon::parse($break->break_start));
                });
                $total = $workedMinutes - $breakMinutes;
                echo floor($total / 60) . ':' . str_pad($total % 60, 2, '0', STR_PAD_LEFT);
                @endphp
                @else
                ---
                @endif
            </td>
            <td>
                <a href="{{ route('staff.attendance.show', ['date' => $targetDateCarbon->format('Y-m-d'), 'user_id' => $user->id]) }}">詳細</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection