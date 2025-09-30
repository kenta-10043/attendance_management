@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/attendance_list.css') }}">
@endsection

@section('content')
    <div>
        <a class="link__previous" href="{{ route('attendance.list', ['date' => $prev->format('Y-m')]) }}">←前月</a>
        <a class="link__this" href="{{ route('attendance.list') }}"><img src="{{ asset('storage/images/image 1.png') }}"
                alt="カレンダー画像">{{ $title }}</a>
        <a class="link__next" href="{{ route('attendance.list', ['date' => $next->format('Y-m')]) }}">翌月→</a>
    </div>

    <div class="calender">
        <div class="calendar__content">

            <span class="attendance__items">日付</span>
            <span class="attendance__items">出勤</span>
            <span class="attendance__items">退勤</span>
            <span class="attendance__items">休憩</span>
            <span class="attendance__items">合計</span>
            <span class="attendance__items">詳細</span>



            @foreach ($dailyAttendances as $attendance)
                <div class="attendance__date">{{ $attendance['date']->isoFormat('MM/DD (ddd)') }}</div>
                <div>{{ $attendance['clock_in'] ?? '' }}</div>

                <div>2</div>
                <div>3</div>
                <div>4</div>
                <a class="link__detail" href="##">詳細</a>
            @endforeach


        </div>

    </div>

@endsection
