@extends('layouts.auth')

@section('title', '会員登録')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
    <div class="main__content">
        <form class="register__form" action="/register" method="POST" novalidate>
            @csrf

            <h1 class="register-form__title">会員登録</h1>

            <div class="register__items">
                <div class="register__inner">
                    <label class="item-label" for="name">名前</label>
                    <input class="input__form" name="name" id="name" type="text" value="{{ old('name') }}"
                        placeholder="お名前を入力してください">
                    <div class="form__error">
                        @error('name')
                            {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="register__inner">
                    <label class="item-label" for="email">メールアドレス</label>
                    <input class="input__form" name="email" id="email" type="email" value="{{ old('email') }}"
                        placeholder="メールアドレスを入力してください">
                    <div class="form__error">
                        @error('email')
                            {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="register__inner">
                    <label class="item-label" for="password">パスワード</label>
                    <input class="input__form" name="password" id="password" type="password" placeholder="パスワードを入力してください">
                    <div class="form__error">
                        @error('password')
                            {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="register__inner">
                    <label class="item-label" for="password_confirm">パスワード確認</label>
                    <input class="input__form" name="password_confirmation" id="password_confirm" type="password"
                        placeholder="もう一度パスワードを入力してください">
                </div>
            </div>


            <div class="navigation-item">
                <button class="register__button" type="submit">登録する</button>
                <p><a class="login__link" href="/login">ログインはこちら</a></p>
            </div>
        </form>

    </div>

@endsection
