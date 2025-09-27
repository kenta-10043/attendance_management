<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/auth.css') }}">
    @yield('css')
</head>

<body>
    @if (Auth::check() && Auth::user()->hasVerifiedEmail())
        <header class="main-header">
            <a class="title-name" href="{{ route('attendance.attendance') }}"><img class="title"
                    src="{{ asset('storage/images/CoachTech_White 1.png') }}" alt="ロゴ"></a>
        </header>
    @elseif(Auth::check())
        <header class="main-header">
            <a class="title-name" href="/email/verify">
                <img class="title" src="{{ asset('storage/images/CoachTech_White 1.png') }}" alt="ロゴ">
            </a>
        </header>
    @else
        <header class="main-header">
            <a class="title-name" href="/login"><img class="title"
                    src="{{ asset('storage/images/CoachTech_White 1.png') }}" alt="ロゴ"></a>
        </header>
    @endif
    @yield('content')

</body>

</html>
