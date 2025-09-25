@extends('layouts.auth')

@section('title', 'ログイン')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
    <div class="main__content">
        <form class="login__form" action="/login" method="POST" novalidate>
            @csrf
            <h1 class="login-form__title">ログイン</h1>

            <div class="login__items">
                <div class="login__inner">
                    <label class="item-label" for="email">メールアドレス</label>
                    <input class="input__form" name="email" id="email" type="email" value="{{ old('email') }}"
                        placeholder="メールアドレスを入力してください">
                    <div class="form__error">
                        @error('email')
                            {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="login__inner">
                    <label class="item-label" for="password">パスワード</label>
                    <input class="input__form" name="password" id="password" type="password" placeholder="パスワードを入力してください">
                    <div class="form__error">
                        @error('password')
                            {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>

            <div class="navigation-item">
                <button class="login__button" type="submit">ログインする</button>
                <p><a class="register__link" href="/register">会員登録はこちら</a></p>
            </div>
        </form>


    @endsection
