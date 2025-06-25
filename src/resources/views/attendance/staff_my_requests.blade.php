<!-- app.blade.phpを呼び出し -->
@extends('layouts.app')

@section('css')
<!-- このページで使用するcssを呼び出し -->
<link rel="stylesheet" href="{{ asset('css/staff_my_Requests.css') }}" />
@endsection







<!-- 本体 -->
@section('content')

<h2>申請一覧</h2>

<div class="status-links" style="margin-bottom: 10px;">
    <a href="{{ route('staff.attendance.myRequest', ['status' => 'waiting']) }}">承認待ち</a> |
    <a href="{{ route('staff.attendance.myRequest', ['status' => 'approved']) }}">承認済み</a>
</div>


<table class="application-table">
    @if ($status === 'waiting')
    <h3>承認待ち</h3>
    <table class="application-table">
        <thead>
            <tr>
                <th>ステータス</th>
                <th>申請理由</th>
                <th>対象日</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($waitingApplications as $app)
            <tr>
                <td>承認待ち</td>
                <td>{{ $app->reason }}</td>
                <td>{{ \Carbon\Carbon::parse(optional($app->attendance)->work_date)->format('Y/m/d') }}</td>
                <td>{{ $app->created_at->format('Y/m/d') }}</td>
                <td>
                    <a href="{{ route('staff.attendance.show', ['date' => optional($app->attendance)->work_date]) }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if ($status === 'approved')
    <h3>承認済み</h3>
    <table class="application-table">
        <thead>
            <tr>
                <th>ステータス</th>
                <th>申請理由</th>
                <th>対象日</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($approvedApplications as $app)
            <tr>
                <td>承認済み</td>
                <td>{{ $app->reason }}</td>
                <td>{{ \Carbon\Carbon::parse(optional($app->attendance)->work_date)->format('Y/m/d') }}</td>
                <td>{{ $app->created_at->format('Y/m/d') }}</td>
                <td>
                    <a href="{{ route('staff.attendance.show', ['date' => optional($app->attendance)->work_date]) }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

</table>


@endsection