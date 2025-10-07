@extends('layouts.admin')

@section('title', '勤怠一覧（管理者）')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/admin_attendance_list.css') }}">
@endsection

@section('content')
    <h2 class="attendance__tittle">{{ $date }}の勤怠一覧</h2>

    <div class="calendar">
        <table class="calendar__content">
            <tr>
                <th class="attendance__item">名前</th>
                <th class="attendance__item">出勤</th>
                <th class="attendance__item">退勤</th>
                <th class="attendance__item">休憩</th>
                <th class="attendance__item">合計</th>
                <th class="attendance__item">詳細</th>
            </tr>


            @foreach ($attendances as $attendance)
                <tr>

                    <td class="attendance__item__data">{{ optional($attendance->user)->name }} </td>
                    <td class="attendance__item__data">
                        {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }} </td>
                    <td class="attendance__item__data">
                        {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}
                    </td>
                    <td class="attendance__item__data"></td>
                    <td class="attendance__item__data"></td>
                    {{-- <form action="{{ route('attendance.detail', ['id' => $pendingAttendance->id]) }}" method="GET">
                        <td class="attendance__item__data"> <button class="button__detail" type="submit">詳細</button>
                        </td>
                    </form> --}}

                </tr>
            @endforeach
        </table>
    </div>
@endsection
