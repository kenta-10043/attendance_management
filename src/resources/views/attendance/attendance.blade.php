@extends('layouts.app')

@section('title', '出勤登録')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/attendance.css') }}">
@endsection

@section('content')
    @if (session('message'))
        <div class="alert-success">
            {{ session('message') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert-error">
            {{ session('error') }}
        </div>
    @endif

    <div class="main__content">
        <span class="attendance__status">{{ $statusLabel }} </span>
        <div class="date-time">
            <span class="date-today">{{ $now }} </span>
            <span class="time-today" id="current-time">{{ $today->format('H:i') }}</span>
        </div>

        @if (!$attendance || $statusLabel === '勤務外')
            <div>
                <form action="{{ route('attendance.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="start">
                    <button class="button__attendance" type="submit">出勤</button>
                </form>
            </div>
        @endif

        @if ($attendance && $attendance->isWorking())
            <div class="attendance__content">
                <div>
                    <form action="{{ route('attendance.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="type" value="end">
                        <button class="button__attendance-finish" type="submit">退勤</button>
                    </form>
                </div>

                <div class="attendance__content">
                    <form action="{{ route('attendance.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="type" value="break_start">
                        <button class="button__break-start" type="submit">休憩入</button>
                    </form>
                </div>
            </div>
        @endif

        @if ($attendance && $attendance->isOnBreak())
            <div>
                <form method="POST" action="{{ route('attendance.store') }}"> @csrf
                    <input type="hidden" name="type" value="break_end">
                    <button class="button__break-end" type="submit">休憩戻</button>
                </form>
            </div>
        @endif

        @if ($statusLabel === '退勤済')
            <p class="finished__message">お疲れ様でした。</p>
        @endif
    </div>


    <script>
        (function() {
            const serverTime = new Date("{{ $today->toDateTimeString() }}");
            const clientTime = new Date();
            const timeDiff = serverTime.getTime() - clientTime.getTime();

            function updateTime() {
                const now = new Date(new Date().getTime() + timeDiff);
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                document.getElementById('current-time').textContent = `${hours}:${minutes}`;
            }

            updateTime();

            const nowClient = new Date(new Date().getTime() + timeDiff);
            const delay = (60 - nowClient.getSeconds()) * 1000;

            setTimeout(() => {
                updateTime();
                setInterval(updateTime, 60000);
            }, delay);
        })();
    </script>
@endsection
