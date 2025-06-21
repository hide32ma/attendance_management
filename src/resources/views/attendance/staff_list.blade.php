<!-- app.blade.phpを呼び出し -->
@extends('layouts.app')

@section('css')
<!-- このページで使用するcssを呼び出し -->
<link rel="stylesheet" href="{{ asset('css/staff_index.css') }}" />
@endsection






<!-- 本体 -->
@section('content')

<div>勤務一覧画面（一般ユーザー）</div>
<div>ログイン時のみ表示</div>

<div>
    <h2>勤務一覧</h2>


    <!-- このルートを使うためにweb.phpにnameをつけている -->
    <div class="calendar-nav">
        <a href="{{ route('staff.attendance.list', ['year' => $current->copy()->subMonth()->year, 'month' => $current->copy()->subMonth()->month]) }}">← 前月</a>
        <!-- 改行させない -->
        <span>{{ $current->format('Y年m月') }}</span>
        <!-- このルートを使うためにweb.phpにnameをつけている -->
        <a href="{{ route('staff.attendance.list', ['year' => $current->copy()->addMonth()->year, 'month' => $current->copy()->addMonth()->month]) }}">翌月 →</a>
    </div>

    <table>
        <tr>
            <th>日付</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
        @foreach ($attendances as $attendance)
        <tr>
            <!-- 日付(work_date)データ -->
            <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('m/d') }}({{ ['日','月','火','水','木','金','土'][\Carbon\Carbon::parse($attendance->work_date)->dayOfWeek] }})</td>

            <!-- 出勤(clock_in)データ -->
            <td>
                {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}
            </td>
            <!-- 退勤(clock_out)データ -->
            <td>
                {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}
            </td>


            <td>未計算</td>
            <td>未計算</td>
            <td><a href="#">詳細</a></td>
        </tr>
        @endforeach
    </table>
</div>


@endsection