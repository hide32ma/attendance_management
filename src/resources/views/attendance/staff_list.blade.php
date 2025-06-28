<!-- app.blade.phpを呼び出し -->
@extends('layouts.app')

@section('css')
<!-- このページで使用するcssを呼び出し -->
<link rel="stylesheet" href="{{ asset('css/staff_list.css') }}" />
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
        <span>{{ $current->format('Y/m') }}</span>
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

        @php
        $weekMap = ['日', '月', '火', '水', '木', '金', '土'];
        @endphp

        @foreach ($daysInMonth as $day)
        @php
        $dateKey = $day->toDateString();
        $attendance = $attendances[$dateKey] ?? null;
        $weekday = $weekMap[$day->dayOfWeek];
        @endphp
        <tr>
            <td>{{ $day->format('m/d') }} ({{ $weekday }})</td>

            <!-- 出勤時間 -->
            <td>
                @if ($attendance && $attendance->clock_in)
                {{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}
                @endif
            </td>

            <!-- 退勤時間 -->
            <td>
                @if ($attendance && $attendance->clock_out)
                {{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}
                @endif
            </td>

            <!-- 休憩の合計時間 -->
            <td>
                @if ($attendance && $attendance->breakTimes->isNotEmpty())
                        @php
                            $totalBreakMinutes = $attendance->breakTimes->sum(function($break) {
                                return \Carbon\Carbon::parse($break->break_end)
                                    ->diffInMinutes(\Carbon\Carbon::parse($break->break_start));
                            });
                            echo floor($totalBreakMinutes / 60) . ':' . str_pad($totalBreakMinutes % 60, 2, '0', STR_PAD_LEFT);
                        @endphp
                    @endif
            </td>
            <!-- 勤務合計時間 -->
            <!-- (出勤 〜 退勤 - 休憩合計時間) -->
            <td>
                @if ($attendance && $attendance->clock_in && $attendance->clock_out)
                @php
                $start = \Carbon\Carbon::parse($attendance->clock_in);
                $end = \Carbon\Carbon::parse($attendance->clock_out);
                $workedMinutes = $start->diffInMinutes($end);

                $breakMinutes = $attendance->breakTimes->sum(function($break) {
                return \Carbon\Carbon::parse($break->break_end)->diffInMinutes(\Carbon\Carbon::parse($break->break_start));
                });

                $totalMinutes = $workedMinutes - $breakMinutes;

                echo floor($totalMinutes / 60) . ':' . str_pad($totalMinutes % 60, 2, '0', STR_PAD_LEFT);
                @endphp
                @endif
            </td>

            <!-- 詳細リンク -->
            <td>

                <a href="{{ route('staff.attendance.show', ['date' => $day->format('Y-m-d')]) }}">詳細</a>


            </td>

        </tr>
        @endforeach


    </table>
</div>


@endsection