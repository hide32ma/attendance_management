<!-- app.blade.phpを呼び出し -->
@extends('layouts.app')

@section('css')
<!-- このページで使用するcssを呼び出し -->
<link rel="stylesheet" href="{{ asset('css/staff_my_Requests.css') }}" />
@endsection

<!-- 本体 -->
@section('content')

<!-- 管理者(admin)ログイン中 -->
@if (Auth::guard('admin')->check())

<h2>申請一覧（管理者）</h2>

<div class="status-links" style="margin-bottom: 10px;">
    <a href="{{ route('staff.attendance.myRequest', ['status' => 'waiting']) }}">承認待ち</a> |
    <a href="{{ route('staff.attendance.myRequest', ['status' => 'approved']) }}">承認済み</a>
</div>


@if ($status === 'waiting')
<h3>承認待ち</h3>
<table class="application-table">
    <thead>
        <tr>
            <th>ステータス</th>
            <th>名前</th>
            <th>対象日付</th>
            <th>申請理由</th>
            <th>申請日</th>
            <th>詳細</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($waitingApplications as $app)
        <tr>
            {{-- ステータス --}}
            <td>{{ $app->status === 0 ? '承認待ち' : ($app->status === 1 ? '承認済み' : '不明') }}</td>

            {{-- 名前 --}}
            <td>{{ $app->user->name }}</td>

            {{-- 対象日付 --}}
            <td>{{ \Carbon\Carbon::parse(optional($app->attendance)->work_date)->format('Y/m/d') }}</td>

            {{-- 申請理由 --}}
            <td>{{ $app->reason }}</td>

            {{-- 申請日 --}}
            <td>{{ $app->created_at->format('Y/m/d') }}</td>

            <td>
                {{-- 安全にフォーマットして渡す --}}
                {{-- ここのリンクから飛んだときは、修正ボタンが承認ボタンになる --}}
                @php
                $workDate = optional($app->attendance)->work_date;
                $formattedDate = $workDate ? \Carbon\Carbon::parse($workDate)->format('Y-m-d') : '';
                @endphp

                <a href="{{ route('admin.attendance.show', [
                'user_id' => $app->user_id,
                'date' => $formattedDate,
                'from' => 'approval'
            ]) }}">詳細</a>
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
            <th>名前</th>
            <th>対象日付</th>
            <th>申請理由</th>
            <th>申請日</th>
            <th>詳細</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($approvedApplications as $app)
        <tr>
            <td>{{ $app->status === 0 ? '承認待ち' : ($app->status === 1 ? '承認済み' : '不明') }}</td>
            <td>{{ $app->user->name }}</td>
            <!-- <td>{{ optional($app->attendance)->work_date }}</td> -->
            <td>{{ \Carbon\Carbon::parse(optional($app->attendance)->work_date)->format('Y/m/d') }}</td>
            <td>{{ $app->reason }}</td>
            <td>{{ $app->created_at->format('Y/m/d') }}</td>
            <td>
                <a href="{{ route('admin.attendance.show', ['user_id' => $app->user_id, 'date' => optional($app->attendance)->work_date]) }}">詳細</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif


<!-- 一般ユーザー(staff)ログイン中 -->
@elseif (Auth::guard('web')->check())

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

@endif


@endsection