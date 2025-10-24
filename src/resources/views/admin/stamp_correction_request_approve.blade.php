@extends('layouts.admin')

@section('title', '修正申請承認（管理者）')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/admin_stamp_correction_request_approve.css') }}">
@endsection

@section('content')

    @if (session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="title__inner">
        <h2 class="attendance__tittle">勤怠詳細</h2>
    </div>

    <div class="main__content">
        <div class="attendance__content">

            <div class="attendance__item">
                <label class="item__label" for="name">名前</label>
                <p class="name" id='name'>{{ $userName }}</p>
            </div>

            <div class="attendance__item" id='date'>
                <label class="item__label" for="date">日付</label>
                <p class="date-year">{{ $attendanceDate->isoFormat('YYYY年') }} </p>
                <p class="date-month">{{ $attendanceDate->isoFormat('M月D日') }} </p>
            </div>



            <form action="{{ route('admin.admin_storeApprove', ['attendance_correct_request_id' => $application->id]) }}"
                method="POST">
                @csrf
                <div class="attendance__item" id='clock'>
                    <label class="item__label" for="clock">出勤・退勤</label>
                    <input class="input__time-in" type="text" name="clock_in"
                        value="{{ old('clock_in') ??
                            ($applicationClockIn
                                ? $applicationClockIn->format('H:i')
                                : ($attendanceClockIn
                                    ? $attendanceClockIn->format('H:i')
                                    : '')) }}"
                        @if (in_array(optional($attendance->application)->approval, [1, 2])) readonly @endif>

                    <span>～</span>

                    <input class="input__time-out" type="text" name="clock_out"
                        value="{{ old('clock_out') ??
                            ($applicationClockOut
                                ? $applicationClockOut->format('H:i')
                                : ($attendanceClockOut
                                    ? $attendanceClockOut->format('H:i')
                                    : '')) }}"
                        @if (in_array(optional($attendance->application)->approval, [1, 2])) readonly @endif>

                </div>

                <div id="break">
                    @php
                        $breakStart = $applicationStartBreaks->isNotEmpty()
                            ? $applicationStartBreaks
                            : $attendanceStartBreaks;
                        $breakEnd = $applicationEndBreaks->isNotEmpty() ? $applicationEndBreaks : $attendanceEndBreaks;
                    @endphp

                    @foreach ($breakStart->zip($breakEnd) as $pair)
                        @php
                            $startBreak = $pair[0];
                            $endBreak = $pair[1];
                        @endphp
                        <div class="attendance__item">
                            <label class="item__label">休憩{{ $loop->iteration }}</label>
                            <input class="input__time-start" type="text" name="start_break[]"
                                value="{{ old('start_break.' . $loop->index, optional($startBreak)->format('H:i')) }}"
                                @if (in_array(optional($attendance->application)->approval, [1, 2])) readonly @endif>
                            <span>～</span>
                            <input class="input__time-end" type="text" name="end_break[]"
                                value="{{ old('end_break.' . $loop->index, optional($endBreak)->format('H:i')) }}"
                                @if (in_array(optional($attendance->application)->approval, [1, 2])) readonly @endif>
                        </div>
                    @endforeach

                    {{-- 新しい休憩追加用 --}}
                    @if (!in_array(optional($attendance->application)->approval, [1, 2]))
                        <div class="attendance__item">
                            <label class="item__label">休憩{{ $breakStart->count() + 1 }}</label>
                            <input class="input__time-start" type="text" name="start_break[]"
                                value="{{ old('start_break.' . $breakStart->count()) }}">
                            <span>～</span>
                            <input class="input__time-end" type="text" name="end_break[]"
                                value="{{ old('end_break.' . $breakStart->count()) }}">
                        </div>
                    @endif
                </div>

                <div class="attendance__item-notes">
                    <label class="item__label" for="notes">備考</label>
                    <textarea class="attendance__notes" name="notes" id="notes" cols="30" rows="3"
                        @if (in_array(optional($attendance->application)->approval, [1, 2])) readonly @endif>{{ old('notes', optional($application)->notes) }}</textarea>
                </div>
        </div>
    </div>

    @if (optional($attendance->application)->approval === 1)
        <div class="button__area">
            <button class="button__approval" type="submit">承認</button>
        </div>
    @elseif(optional($attendance->application)->approval === 2)
        <div class="button__area">
            <button class="button__approved" type="submit" disabled>承認済み</button>
        </div>
    @endif

    </form>


@endsection
