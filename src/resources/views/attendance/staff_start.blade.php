<!-- app.blade.phpを呼び出し -->
@extends('layouts.app')

@section('css')
<!-- このページで使用するcssを呼び出し -->
<link rel="stylesheet" href="{{ asset('css/staff_start.css') }}" />
@endsection






<!-- 本体 -->
@section('content')

<div>勤怠登録画面（一般ユーザー）</div>



<div>
    <!-- 勤務ステータスが表示される -->
    @foreach ($attendances as $attendance)
    <tr>
        <td>{{ $attendance->user->name }}</td>
        <td>{{ $attendance->status_label }}</td>
    </tr>
    @endforeach

    <!-- 現在の時間が表示される -->
    <div>{!! nl2br(e($nowDateTime)) !!}</div>

    <!-- 出勤ボタン -->
    @if($attendance && $attendance->status === \App\Models\Attendance::STATUS_OFF)
    <form method="post" action="/attendance">
        @csrf
        <button type="submit">出勤</button>
    </form>
    @endif


</div>

@endsection