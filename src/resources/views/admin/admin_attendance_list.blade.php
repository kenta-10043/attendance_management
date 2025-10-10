@extends('layouts.admin')

@section('title', '勤怠一覧（管理者）')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/admin_attendance_list.css') }}">
@endsection

@section('content')
    <h2 class="attendance__tittle">{{ \Carbon\Carbon::parse($date)->isoFormat('YYYY年MM月D日') }}の勤怠一覧</h2>

    <div class="calendar">

        <div class="calendar__date">
            <a class="link__previous" href="{{ route('admin.admin_attendance_list', ['date' => $prev]) }}">←前日</a>
            <a class="link__this" href="{{ route('admin.admin_attendance_list') }}"><img class="calendar__image"
                    src="{{ asset('storage/images/image 1.png') }}"
                    alt="カレンダー画像">{{ \Carbon\Carbon::parse($date)->format('Y/m/d') }}</a>
            <a class="link__next" href="{{ route('admin.admin_attendance_list', ['date' => $next]) }}">翌日→</a>
        </div>

        <table class="calendar__content">
            <tr>
                <th class="attendance__item">名前</th>
                <th class="attendance__item">出勤</th>
                <th class="attendance__item">退勤</th>
                <th class="attendance__item">休憩</th>
                <th class="attendance__item">合計</th>
                <th class="attendance__item">詳細</th>
            </tr>


            @foreach ($userAttendances as $userAttendance)
                <tr>

                    <td class="attendance__item__data">{{ $userAttendance->user_name }}</td>
                    <td class="attendance__item__data">
                        {{ $userAttendance->clock_in ?? '' }}
                    </td>
                    <td class="attendance__item__data">
                        {{ $userAttendance->clock_out ?? '' }}
                    </td>
                    <td class="attendance__item__data">
                        {{ $userAttendance->break ?? '' }}
                    </td>
                    <td class="attendance__item__data">
                        {{ $userAttendance->work ?? '' }}
                    </td>
                    <td class="attendance__item__data">
                        <form action="{{ route('admin.admin_attendance_detail', ['id' => $userAttendance->id]) }}"
                            method="GET">
                            <button class="button__detail" type="submit">詳細</button>
                        </form>
                    </td>

                </tr>
            @endforeach
        </table>
    </div>
@endsection
