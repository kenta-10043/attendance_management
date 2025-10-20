@extends('layouts.admin')

@section('title', 'スタッフ一覧（管理者）')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/admin_staff_list.css') }}">
@endsection

@section('content')

    <div class="title__inner">
        <h2 class="attendance__tittle">スタッフ一覧</h2>
    </div>

    <div class="calendar">

        <table class="calendar__content">
            <tr>
                <th class="attendance__item">名前</th>
                <th class="attendance__item">メールアドレス</th>
                <th class="attendance__item">月次勤怠</th>

            </tr>


            @foreach ($users as $user)
                <tr>

                    <td class="attendance__item__data">{{ $user->name }}</td>
                    <td class="attendance__item__data">
                        {{ $user->email }}
                    </td>

                    <td class="attendance__item__data">
                        <form action="{{ route('admin.admin_attendance_index', ['id' => $user->id]) }}" method="GET">
                            <button class="button__detail" type="submit">月次勤怠</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>

@endsection
