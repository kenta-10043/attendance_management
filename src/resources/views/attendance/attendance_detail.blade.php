@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/attendance_detail.css') }}">
@endsection

@section('content')
    <h2 class="attendance__tittle">勤怠一覧</h2>

    <div class="main__content">
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




            <form action="{{ route('attendance.updateOrCreate', ['id' => $attendance->id ?? null]) }}" method="POST">
                @csrf
                <div class="attendance__item" id='clock'>
                    <label class="item__label" for="clock">出勤・退勤</label>
                    <input class="input__time-in" type="time" name="clock_in"
                        value="{{ old('clock_in', optional($attendance)->clock_in?->format('H:i')) }}">
                    <span>～</span>
                    <input class="input__time-out" type="time" name="clock_out"
                        value="{{ old('clock_out', optional($attendance)->clock_out?->format('H:i')) }}">



                </div>
                <div class="form__error">
                    @error('clock_in')
                        {{ $message }}
                    @enderror
                </div>
                <div class="form__error">
                    @error('clock_out')
                        {{ $message }}
                    @enderror
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
                                value="{{ old('start_break.' . $loop->index, optional($startBreak)->format('H:i')) }}">
                            <span>～</span>
                            <input class="input__time-end" type="time" name="end_break[]"
                                value="{{ old('end_break.' . $loop->index, optional($endBreak)->format('H:i')) }}">



                        </div>
                        <div class="form__error">
                            @error('start_break.' . $loop->index)
                                {{ $message }}
                            @enderror
                        </div>
                        <div class="form__error">
                            @error('end_break.' . $loop->index)
                                {{ $message }}
                            @enderror
                        </div>
                    @endforeach

                    <div class="attendance__item">
                        <label class="item__label" for="break">休憩{{ $attendanceStartBreaks->count() + 1 }}</label>
                        <input class="input__time-start" type="time" name="start_break[]"
                            value="{{ old('start_break.' . $attendanceStartBreaks->count()) }}">
                        <span>～</span>
                        <input class="input__time-end" type="time" name="end_break[]"
                            value="{{ old('end_break.' . $attendanceStartBreaks->count()) }}">


                    </div>
                    <div class="form__error">
                        @error('start_break.' . $attendanceStartBreaks->count())
                            {{ $message }}
                        @enderror
                    </div>
                    <div class="form__error">
                        @error('end_break.' . $attendanceStartBreaks->count())
                            {{ $message }}
                        @enderror
                    </div>
                </div>


                <div class="attendance__item-notes">
                    <label class="item__label" for="notes">備考</label>
                    <textarea class="attendance__notes" name="notes" id="notes" cols="30" rows="3">{{ old('notes') }}</textarea>
                </div>
                <div class="form__error">
                    @error('notes')
                        {{ $message }}
                    @enderror
                </div>
        </div>
    </div>
    <input type="hidden" name="approval" value="1">
    <div class="button__area">
        <button class="button__update" type="submit">修正</button>
    </div>

    </form>


@endsection
