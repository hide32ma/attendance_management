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
                <a href="">
                    <img src="{{ asset('img/logo.svg') }}" alt="COACHTECH">
                </a>
            </h1>
            <ul class="header-nav">
                <!-- ログイン済みの時だけ表示する -->
                <!-- 一般ユーザー用 -->
                @auth
                <li class="header-nav__item">
                    <!-- ログアウト機能 -->
                    <form class="logout__form" action="/logout" method="post">
                        @csrf
                        <button class="logout__button">ログアウト</button>
                    </form>
                </li>
                @endauth

                <!-- 管理者用 -->
                <!-- @auth('admin') -->
                <!-- <li class="header-nav__item"> -->
                    <!-- ログアウト機能 -->
                    <!-- <form class="logout__form" action="/logout" method="post"> -->
                        <!-- @csrf -->
                        <!-- <button class="logout__button">ログアウト</button> -->
                    <!-- </form> -->
                <!-- </li> -->
                <!-- @endauth -->
            </ul>
        </header>
    </div>

    <main>

        <!-- メインコンテンツ -->
        @yield('content')


    </main>
</body>

</html>