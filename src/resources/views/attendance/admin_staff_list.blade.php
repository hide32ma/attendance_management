<!-- app.blade.phpを呼び出し -->
@extends('layouts.app')

@section('css')
<!-- このページで使用するcssを呼び出し -->
<link rel="stylesheet" href="{{ asset('css/admin_staff_list.css') }}" />
@endsection

<!-- 本体 -->
@section('content')


<h2>スタッフ一覧</h2>


<table>
    <thead>
        <tr>
            <th>名前</th>
            <th>メールアドレス</th>
            <th>詳細</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($staffs as $staff)
        <tr>
            <td>{{ $staff->name }}</td>
            <td>{{ $staff->email }}</td>
            <td><a href="{{ route('admin.attendance.staff', ['user' => $staff->id]) }}">詳細</a></td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection