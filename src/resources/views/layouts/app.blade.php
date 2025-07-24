<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>attendance_management</title>

    <!-- sanitize.css呼び出し -->
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />

    <!-- common.css呼び出し -->
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" />

    <!-- ページによって呼び出すcssは違います -->
    @yield('css')
</head>

<body>
    <div class="app">
        <header class="header">
            <h1 class="header-heading">
                <a href="/">
                <img src="{{ asset('img/logo.svg') }}" alt="COACHTECH">
                </a>
            </h1>
            <ul class="header-nav">
                <!-- 管理者(admin)ログイン中 -->
                @if (Auth::guard('admin')->check())
                <li><a href="/admin/attendance/list" class="link">勤務一覧</a></li>
                <li><a href="/admin/staff/list" class="link">スタッフ一覧</a></li>
                <li><a href="/staff/stamp_correction_request/list" class="link">申請</a></li>
                <!-- 一般ユーザー(staff)ログイン中 -->
                @elseif (Auth::guard('web')->check())
                <li><a href="/" class="link">勤怠</a></li>
                <li><a href="/staff/attendance/list" class="link">勤怠一覧</a></li>
                <li><a href="/staff/stamp_correction_request/list" class="link">申請</a></li>
                @endif

                <!-- ログアウトボタンは共通で表示 -->
                <!-- ログインしている人だけログアウトボタンを表示 -->
                @if (Auth::guard('admin')->check() || Auth::guard('web')->check())
                <li class="header-nav__item">
                    <form class="logout_form" action="/logout" method="post">
                        @csrf
                        <button class="logout_button">ログアウト</button>
                    </form>
                </li>
                @endif
            </ul>
        </header>
    </div>

    <main>

        <!-- メインコンテンツ -->
        @yield('content')


    </main>
</body>

</html>