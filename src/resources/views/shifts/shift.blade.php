@extends('layouts.app')

@section('title', 'シフト一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/shifts/shift.css') }}">
@endsection

@section('content')

    @if (session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="title__inner">
        <h2 class="attendance__title">シフト予定</h2>
    </div>

    <h2>{{ $title }}</h2>

    <table class="shift__table">


        <tr class="shift__table-row">
            <th class="shift__table-header">日付</th>
            @foreach ($days as $day)
                <th class="shift__table-header">{{ $day->isoFormat('ddd') }}</th>
            @endforeach
        </tr>
        <tr class="shift__table-row">
            <th class="shift__table-header">曜日</th>
            @foreach ($days as $day)
                <th class="shift__table-header">{{ $day->format('j') }}</th>
            @endforeach
        </tr>
        <tr class="shift__table-row">
            <th class="shift__table-header">予定</th>
            <td class="shift__table-data"></td>
        </tr>


        @foreach ($users as $user)
            <tr class="shift__table-row">
                <th class="shift__table-header">{{ $user->name }}</th>

                @foreach ($days as $day)
                    @php
                        $dayString = $day->toDateString();
                        $shifts = $userSchedules[$user->id][$dayString];
                    @endphp

                    <td class="shift__table-data">
                        @foreach ($shifts as $shift)
                            {{ $shift->shift_pattern }}
                        @endforeach
                    </td>
                @endforeach
            </tr>
        @endforeach



    </table>
@endsection
