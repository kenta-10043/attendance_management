@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/attendance_detail.css') }}">
@endsection

@section('content')
    <h2 class="attendance__tittle">勤怠一覧</h2>

    <div class="attendance__content">

        <div class="attendance__item">
            <label class="item__label" for="name">名前</label>
            <p class="name" id='name'>{{ $userName }}</p>
        </div>

        <div class="attendance__item" id='date'>
            <label class="item__label" for="date">日付</label>
            <p class="date-year">{{ $attendanceDate->isoFormat('YYYY年') }} </p>
            <p class="date-month">{{ $attendanceDate->isoFormat('MM月DD日') }} </p>
        </div>




        <form action="##">
            <div class="attendance__item" id='clock'>
                <label class="item__label" for="clock">出勤・退勤</label>
                <input class="input__time-in" type="time" name="clock_in"
                    value="{{ old('clock_in', optional($attendanceClockIn)->format('H:i')) }}">
                <span>～</span>
                <input class="input__time-out" type="time" name="clock_out"
                    value="{{ old('clock_out', optional($attendanceClockOut)->format('H:i')) }}">

            </div>

            <div id='break'>
                @foreach ($attendanceStartBreaks->zip($attendanceEndBreaks) as $pair)
                    @php
                        $startBreak = $pair[0];
                        $endBreak = $pair[1];
                    @endphp
                    <div class="attendance__item">
                        <label class="item__label" for="break">休憩{{ $loop->iteration }}</label>
                        <input class="input__time-start" type="time" name="start_break[]"
                            value="{{ optional($startBreak)->format('H:i') }}">
                        <span>～</span>
                        <input class="input__time-end" type="time" name="end_break[]"
                            value="{{ optional($endBreak)->format('H:i') }}">
                    </div>
                @endforeach

                <div class="attendance__item">
                    <label class="item__label" for="break">休憩{{ $attendanceStartBreaks->count() + 1 }}</label>
                    <input class="input__time-start" type="time" name="start_break" value="{{ old('start_break') }}">
                    <span>～</span>
                    <input class="input__time-end" type="time" name="end_break" value="{{ old('end_break') }}">
                </div>

            </div>


            <div class="attendance__item-notes">
                <label class="item__label" for="notes">備考</label>
                <textarea class="attendance__notes" name="notes" id="notes" cols="30" rows="3"></textarea>
            </div>
    </div>

    <div class="button__area">
        <button class="button__update" type="submit">修正</button>
    </div>

    </form>


@endsection
