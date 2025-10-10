@extends('layouts.app')

@section('title', '申請一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/stamp_correction_request_list.css') }}">
@endsection

@section('content')

    @if (session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <h2 class="attendance__tittle">申請一覧</h2>

    <div class="application__content__tab">
        <a class="pending__tab" href="{{ route('attendance.applicationList', ['tab' => 'pending']) }}">承認待ち</a>
        <a class="approval__tab" href="{{ route('attendance.applicationList', ['tab' => 'approved']) }}">承認済み</a>
    </div>


    <div class="calendar">
        <table class="calendar__content">
            <tr>
                <th class="attendance__item">状態</th>
                <th class="attendance__item">名前</th>
                <th class="attendance__item">対象日時</th>
                <th class="attendance__item">申請理由</th>
                <th class="attendance__item">申請日時</th>
                <th class="attendance__item">詳細</th>
            </tr>

            @if (!request()->has('tab') || request()->query('tab') === 'pending')
                @foreach ($pendingAttendances as $pendingAttendance)
                    <tr>

                        <td class="attendance__item__data">{{ optional($pendingAttendance->application)->label }} </td>
                        <td class="attendance__item__data">{{ optional($pendingAttendance->application->user)->name }} </td>
                        <td class="attendance__item__data">
                            {{ $pendingAttendance->date ? \Carbon\Carbon::parse($pendingAttendance->date)->format('Y/m/d') : '' }}
                        </td>
                        <td class="attendance__item__data">{{ optional($pendingAttendance->application)->notes }} </td>
                        <td class="attendance__item__data">
                            {{ optional($pendingAttendance->application->applied_at)
                                ? \Carbon\Carbon::parse($pendingAttendance->application->applied_at)->format('Y/m/d')
                                : '' }}
                        </td>
                        <form action="{{ route('attendance.detail', ['id' => $pendingAttendance->id]) }}" method="GET">
                            <td class="attendance__item__data"> <button class="button__detail" type="submit">詳細</button>
                            </td>
                        </form>

                    </tr>
                @endforeach


            @endif

            @if (!request()->has('tab') || request()->query('tab') === 'approved')
                @foreach ($approvedAttendances as $approvedAttendance)
                    <tr>

                        <td class="attendance__item__data">{{ optional($approvedAttendance->application)->label }} </td>
                        <td class="attendance__item__data">{{ optional($approvedAttendance->application->user)->name }}
                        </td>
                        <td class="attendance__item__data">
                            {{ $approvedAttendance->date ? \Carbon\Carbon::parse($approvedAttendance->date)->format('Y/m/d') : '' }}
                        </td>
                        <td class="attendance__item__data">{{ optional($approvedAttendance->application)->notes }} </td>
                        <td class="attendance__item__data">
                            {{ optional($approvedAttendance->application->applied_at)
                                ? \Carbon\Carbon::parse($approvedAttendance->application->applied_at)->format('Y/m/d')
                                : '' }}
                        </td>
                        <form action="{{ route('attendance.detail', ['id' => $approvedAttendance->id]) }}" method="GET">
                            <td class="attendance__item__data"> <button class="button__detail" type="submit">詳細</button>
                            </td>
                        </form>

                    </tr>
                @endforeach
        </table>
    </div>
    @endif

@endsection
