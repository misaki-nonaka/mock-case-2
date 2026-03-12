@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
@endsection

@section('content')
    <div class="verify-content">
        <div class="verify-content__inner">
            <p class="verify-message">
                登録していただいたメールアドレスに認証メールを送付しました。<br>
                メール認証を完了してください。
            </p>

            <div class="verify-button">
                <a href="http://localhost:8025">認証はこちらから</a>
            </div>

            @if (session('status') == 'verification-link-sent')
                <p style="color: green;">
                    認証メールを再送しました。
                </p>
            @endif

            <form method="POST" action="{{ route('verification.send') }}" class="verify-resend">
                @csrf
                <button type="submit">
                    認証メールを再送する
                </button>
            </form>
        </div>
    </div>

@endsection