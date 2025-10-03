<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="main-header">
        <nav class="header__nav">
            <img class="title" src="{{ asset('storage/images/CoachTech_White 1.png') }}" alt="ロゴ">
            <div class="link__menu">
                <a class="link__attendance" href="{{ route('attendance.attendance') }}">勤怠</a>
                <a class="link__index" href="{{ route('attendance.list') }}">勤怠一覧</a>
                <a class="link__application" href="#">申請</a>
                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button type="submit" class="button__logout">ログアウト</button>
                </form>
            </div>
        </nav>
    </header>

    @yield('content')

</body>

</html>
