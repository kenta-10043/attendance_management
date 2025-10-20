@extends('layouts.admin')

@section('title', 'スタッフ別勤怠一覧画面（管理者）')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/admin_attendance_staff.css') }}">
@endsection

@section('content')
    <div class="title__inner">
        <h2 class="attendance__tittle">{{ $user->name }}さんの勤怠 </h2>
    </div>
    <div class="calendar">
        <div class="calendar__date">
            <a class="link__previous"
                href="{{ route('admin.admin_attendance_index', ['id' => $user->id, 'date' => $prev->format('Y-m')]) }}">←前月</a>
            <a class="link__this" href="{{ route('admin.admin_attendance_index', ['id' => $user->id]) }}"><img
                    class="calendar__image" src="{{ asset('storage/images/image 1.png') }}"
                    alt="カレンダー画像">{{ $title }}</a>
            <a class="link__next"
                href="{{ route('admin.admin_attendance_index', ['id' => $user->id, 'date' => $next->format('Y-m')]) }}">翌月→</a>
        </div>


        <table class="calendar__content">
            <tr>
                <th class="attendance__item">日付</th>
                <th class="attendance__item">出勤</th>
                <th class="attendance__item">退勤</th>
                <th class="attendance__item">休憩</th>
                <th class="attendance__item">合計</th>
                <th class="attendance__item">詳細</th>
            </tr>


            @foreach ($days as $index => $day)
                <tr>
                    <td class="attendance__item__data">{{ $day->isoFormat('MM/DD(ddd)') }} </td>
                    @php
                        $attendanceTime = $monthly[$index] ?? null;
                        $attendanceId = $attendanceTime['id'] ?? 0;
                    @endphp
                    <td class="attendance__item__data">{{ $attendanceTime['clock_in'] ?? '' }}</td>
                    <td class="attendance__item__data">{{ $attendanceTime['clock_out'] ?? '' }}</td>
                    <td class="attendance__item__data">
                        @if (!empty($attendanceTime['break_original']) && $attendanceTime['break_original'] !== '00:00')
                            {{ $attendanceTime['break_original'] }}
                        @else
                            {{ $attendanceTime['break'] ?? '' }}
                        @endif
                    </td>
                    <td class="attendance__item__data">
                        @if (!empty($attendanceTime) && ($attendanceTime['approval'] ?? null) === 2)
                            {{ $attendanceTime['work'] ?? '' }}
                        @elseif (!empty($attendanceTime) && !empty($attendanceTime['work_original']) && $attendanceTime['work_original'] !== '00:00')
                            {{ $attendanceTime['work_original'] }}
                        @else
                            {{ $attendanceTime['work'] ?? '' }}
                        @endif
                    </td>

                    <td class="attendance__item__data">
                        <form action="{{ route('admin.admin_attendance_detail', ['id' => $attendanceId]) }}"
                            method="GET">
                            <button class="button__detail" type="submit">詳細</button>
                        </form>
                    </td>
                </tr>
            @endforeach

        </table>

        <div class="button__area">
            <a class="csv__button"
                href="{{ route('admin.admin_attendance_index', ['id' => $user->id, 'download' => 1]) }}">CSV出力</a>
        </div>
    </div>



@endsection
