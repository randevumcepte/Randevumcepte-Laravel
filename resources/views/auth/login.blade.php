@extends('layout.layout_login')

@section('content')
<?php
    $salonAdi = \App\Salonlar::where('domain',$_SERVER['HTTP_HOST'])->value('salon_adi');
    $logo = \App\Salonlar::where('domain',$_SERVER['HTTP_HOST'])->value('logo');
?>
<div class="login-wrapper">
    <div class="login-container">
        <!-- Left Side - Hero -->
        <div class="login-header">
            <video class="login-hero-bg login-hero-video"
                   autoplay muted loop playsinline
                   preload="metadata"
                   poster="{{secure_asset('public/img/loginbg.jpg')}}">
                <source src="{{secure_asset('public/videos/login-bg.m4v')}}" type="video/mp4">
            </video>

            <div class="login-hero-text">
                <h1 class="login-title">{{ $salonAdi }}</h1>
            </div>

        </div>

        <!-- Right Side - Form -->
        <div class="login-body">
            <span class="login-deco-plus p1">+</span>
            <span class="login-deco-plus p2">+</span>
            <span class="login-deco-plus p3">+</span>

            <img src="{{secure_asset($logo)}}" alt="{{$salonAdi}}" class="login-mini-logo">

            <h2 class="login-body-title">Hoş Geldiniz!</h2>
            <p class="login-body-subtitle">Hesabınız yok mu? <a href="/register">Üye olun</a></p>

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

                <div class="login-form-group icon-phone">
                    <input
                        name="cep_telefon"
                        type="text"
                        class="login-form-input"
                        id="cep_telefon"
                        placeholder="Cep Telefonu (5XXXXXXXXX)"
                        maxlength="10"
                        required
                        value="{{ old('cep_telefon') }}"
                        autofocus>
                </div>

                <div class="login-form-group icon-lock">
                    <input
                        name="password"
                        type="password"
                        class="login-form-input"
                        id="password"
                        placeholder="Şifre"
                        required>
                </div>

                <div class="login-options">
                    <label class="login-remember">
                        <input type="checkbox" name="remember" value="1">
                        <span>Beni Hatırla</span>
                    </label>
                    <a href="#" class="login-forgot-link" onclick="event.preventDefault(); alert('Şifrenizi unuttuysanız lütfen işletmeniz ile iletişime geçin.');">Şifremi Unuttum</a>
                </div>

                <div class="login-btn-row">
                    <button type="submit" id="girisyap" class="login-btn">Giriş Yap</button>
                </div>

                <div class="login-divider">veya</div>

                <a href="/isletmeyonetim/girisyap" class="login-btn-secondary">
                    <i class="fa fa-briefcase"></i> İşletme Girişi
                </a>
            </form>
        </div>
    </div>
</div>
@endsection
