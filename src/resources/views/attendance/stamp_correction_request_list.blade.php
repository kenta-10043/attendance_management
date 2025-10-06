@extends('layouts.app')

@section('title', '申請一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/stamp_correction_request_list.css') }}">
@endsection

@section('content')

    <h2 class="attendance__tittle">勤怠一覧</h2>

    <table class="calendar__content">
        <tr>
            <th class="attendance__item">状態</th>
            <th class="attendance__item">名前</th>
            <th class="attendance__item">対象日時</th>
            <th class="attendance__item">申請理由</th>
            <th class="attendance__item">申請日時</th>
            <th class="attendance__item">詳細</th>
        </tr>

        @foreach ($attendances as $attendance)
            <tr>

                <td class="attendance__item__data">{{ optional($attendance->application)->label }} </td>
                <td class="attendance__item__data">{{ optional($attendance->application->user)->name }} </td>
                <td class="attendance__item__data">
                    {{ $attendance->date ? \Carbon\Carbon::parse($attendance->date)->format('Y/m/d') : '' }} </td>
                <td class="attendance__item__data">{{ optional($attendance->application)->notes }} </td>
                <td class="attendance__item__data">
                    {{ optional($attendance->application->applied_at)
                        ? \Carbon\Carbon::parse($attendance->application->applied_at)->format('Y/m/d')
                        : '' }}
                </td>
                <form action="{{ route('attendance.detail', ['id' => $attendance->id]) }}" method="GET">
                    <td class="attendance__item__data"> <button class="button__detail" type="submit">詳細</button></td>
                </form>

            </tr>
        @endforeach
    </table>

@endsection
