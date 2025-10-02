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

    <div class="calendar">
        <table class="calendar__content">
            <tr>
                <th class="attendance__items">日付</th>
                <th class="attendance__items">出勤</th>
                <th class="attendance__items">退勤</th>
                <th class="attendance__items">休憩</th>
                <th class="attendance__items">合計</th>
                <th class="attendance__items">詳細</th>
            </tr>


            @foreach ($days as $index => $day)
                <tr>
                    <td>{{ $day->isoFormat('MM/DD(ddd)') }} </td>
                    @php
                        $attendanceTime = $monthly[$index] ?? null;
                    @endphp
                    <td>{{ $attendanceTime['clock_in'] ?? '' }}</td>
                    <td>{{ $attendanceTime['clock_out'] ?? '' }}</td>
                    <td>{{ $attendanceTime['work'] ?? '' }}</td>
                    <td>{{ $attendanceTime['break'] ?? '' }}</td>

                    <td>詳細</td>
                </tr>
            @endforeach






        </table>

    </div>

@endsection
