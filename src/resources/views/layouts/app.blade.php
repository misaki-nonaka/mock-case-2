<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
    <title>coachtech勤怠管理アプリ</title>
</head>

<body>
    <header class="header">
        <div class="header__logo">
            <a href="/"><img src="/COACHTECHヘッダーロゴ.png" alt=""></a>
        </div>
        
        @if( !in_array(Route::currentRouteName(), ['verification.notice']) )
        <nav class="header__nav">
        @if (Auth::guard('admin')->check())
            <ul class="header-nav__inner">
                <li class="header-nav__item">
                    <a href="/admin/attendance/list" class="header-nav__link">勤怠一覧</a>
                </li>
                <li class="header-nav__item">
                    <a href="/admin/staff/list" class="header-nav__link">スタッフ一覧</a>
                </li>
                <li class="header-nav__item">
                    <a href="/stamp_correction_request/list" class="header-nav__link">申請一覧</a>
                </li>
                <li class="header-nav__item">
                    <form action="/logout" method="post" class="header-nav__form">
                    @csrf
                        <button class="header-nav__button">ログアウト</button>
                    </form>
                </li>                
            </ul>
        @endif

        @if (Auth::check())
            <ul class="header-nav__inner">
                <li class="header-nav__item">
                    <a href="/attendance" class="header-nav__link">勤怠</a>
                </li>
                <li class="header-nav__item">
                    <a href="/attendance/list" class="header-nav__link">勤怠一覧</a>
                </li>
                <li class="header-nav__item">
                    <a href="/stamp_correction_request/list" class="header-nav__link">申請</a>
                </li>
                <li class="header-nav__item">
                    <form action="/logout" method="post" class="header-nav__form">
                    @csrf
                        <button class="header-nav__button">ログアウト</button>
                    </form>
                </li>
            </ul>
        @endif
        </nav>
        @endif
    </header>

    <main>
        <div class="content">
            @yield('content')
        </div>
    </main>
</body>
</html>