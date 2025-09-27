@extends('layouts.auth')

@section('title', '認証メール')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/verify.css') }}">
@endsection

@section('content')
    <div class="container">
        <div class="mail-message__contents">

            <p class="send-message">登録していただいたメールアドレスに認証メールを送付しました。<br>メール認証を完了してください。</p>
            <button class="button__verification" onclick="location.href='http://localhost:8025'">認証はこちらから</button>


            <form action="{{ route('verification.send') }}" method="post">
                @csrf
                <button class="verification-send" type="submit">認証メールを再送する</button>
            </form>

        </div>
    </div>

@endsection
