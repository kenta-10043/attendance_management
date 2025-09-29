@extends('layouts.app')

@section('title', '出勤登録')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/admin_attendance.css') }}">
@endsection

@section('content')
    <div>
        <div>{{ $statusLabel }} </div>
        <div>{{ $now }} </div>
        <div id="current-time">{{ $today->format('H:i') }}</div>
    </div>

    <div>
        <form action="{{ route('attendance.store') }}" method="POST">
            @csrf
            <input type="hidden" name="date">
            <input type="hidden" name="clock_in">
            <button type="submit">出勤</button>
        </form>
    </div>

    <div>
        <form action="{{ route('attendance.store') }}" method="POST">
            @csrf
            <input type="hidden" name="clock_out">
            <button type="submit">退勤</button>
        </form>
    </div>



    <script>
        (function() {
            // サーバー時刻とクライアント時刻の差（ミリ秒）
            const serverTime = new Date("{{ $today->toDateTimeString() }}");
            const clientTime = new Date();
            const timeDiff = serverTime.getTime() - clientTime.getTime();

            function updateTime() {
                const now = new Date(new Date().getTime() + timeDiff);
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                document.getElementById('current-time').textContent = `${hours}:${minutes}`;
            }

            // 初回更新（Bladeで初期値表示済み）
            updateTime();

            // 次の分の頭まで待機してから1分ごとに更新
            const nowClient = new Date(new Date().getTime() + timeDiff);
            const delay = (60 - nowClient.getSeconds()) * 1000;

            setTimeout(() => {
                updateTime();
                setInterval(updateTime, 60000); // 毎分更新
            }, delay);
        })();
    </script>
@endsection
