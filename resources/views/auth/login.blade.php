@extends('layout.layout_login')

@section('content')
<div class="login-wrapper">
    <!-- Full Screen Video Background -->
    <video class="login-video-bg" autoplay muted loop playsinline preload="auto">
        <source src="{{secure_asset('public/videos/login-bg.m4v')}}" type="video/mp4">
    </video>

    <!-- Floating Login Card -->
    <div class="login-container">
        <!-- Login Header (Hidden in CSS) -->
        <div class="login-header"></div>

        <!-- Login Body -->
        <div class="login-body">
            <h2 class="login-body-title">Giriş Yap</h2>
            <p class="login-body-subtitle">Hesabınıza erişmek için bilgilerinizi girin</p>

            <form role="form" method="POST" action="{{ route('login') }}">
                {{ csrf_field() }}
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                @if ($errors->has('cep_telefon') || $errors->has('password'))
                <div class="login-error">
                    <svg class="login-error-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="login-error-text">Giriş yapılamadı. Kullanıcı bilgileriniz sistemdekiler ile eşleşmemektedir.</p>
                </div>
                @endif

                <div class="login-form-group">
                    <label for="cep_telefon" class="login-form-label">Cep Telefonu</label>
                    <input
                        name="cep_telefon"
                        type="text"
                        class="login-form-input"
                        id="cep_telefon"
                        placeholder="5XXXXXXXXX"
                        maxlength="10"
                        required
                        value="{{ old('cep_telefon') }}"
                        autofocus>
                </div>

                <div class="login-form-group">
                    <label for="password" class="login-form-label">Şifre</label>
                    <input
                        name="password"
                        type="password"
                        class="login-form-input"
                        id="password"
                        placeholder="••••••••"
                        required>
                </div>

                <button type="submit" id="girisyap" class="login-btn">Giriş Yap</button>

                <div class="login-forgot" style="display: none;">
                    <a href="#">Şifrenizi mi unuttunuz?</a>
                </div>

                <!-- Login Footer - Inside Form -->
                <div class="login-footer">
                    <p class="login-footer-text">Hesabınız yok mu?</p>
                    <a href="/register" class="login-footer-link">Hemen Kayıt Olun</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
