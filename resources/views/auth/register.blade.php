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

            <h2 class="login-body-title">Üye Ol</h2>
            <p class="login-body-subtitle">Zaten üye misiniz? <a href="/login">Giriş yapın</a></p>

            <form role="form" method="POST" action="{{ route('register') }}">
                {{ csrf_field() }}
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                @if ($errors->any())
                <div class="login-error">
                    <svg class="login-error-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="login-error-text">
                        @if($errors->first('email') == 'validation.unique')
                            Girdiğiniz e-posta adresi sistemimizde kayıtlıdır. Giriş yapmak için <a href="/login">tıklayınız</a>.
                        @elseif($errors->first('cep_telefon') == 'validation.unique')
                            Girdiğiniz telefon sistemimizde kayıtlıdır. Giriş yapmak için <a href="/login">tıklayınız</a>.
                        @elseif($errors->first('cep_telefon') == 'validation.regex')
                            Lütfen geçerli bir cep telefon numarası giriniz.
                        @elseif($errors->first('password') == 'validation.confirmed')
                            Girdiğiniz şifreler eşleşmemektedir. Lütfen yeniden deneyiniz.
                        @else
                            {{ $errors->first() }}
                        @endif
                    </p>
                </div>
                @endif

                <div class="login-form-group icon-user">
                    <input
                        name="name"
                        type="text"
                        class="login-form-input"
                        id="name"
                        placeholder="Ad Soyad"
                        required
                        value="{{ old('name') }}"
                        autofocus>
                </div>

                <div class="login-form-group icon-mail">
                    <input
                        name="email"
                        type="email"
                        class="login-form-input"
                        id="email"
                        placeholder="E-posta"
                        value="{{ old('email') }}">
                </div>

                <div class="login-form-group icon-phone">
                    <input
                        name="cep_telefon"
                        type="text"
                        class="login-form-input"
                        id="cep_telefon"
                        placeholder="Cep Telefonu (5XXXXXXXXX)"
                        maxlength="10"
                        pattern="[0-9]*"
                        required
                        value="{{ old('cep_telefon') }}">
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

                <div class="login-form-group icon-lock">
                    <input
                        name="password_confirmation"
                        type="password"
                        class="login-form-input"
                        id="password-confirm"
                        placeholder="Şifre Tekrar"
                        required>
                </div>

                <div class="login-options">
                    <label class="login-remember">
                        <input type="checkbox" name="kosulkabul" value="1" checked>
                        <span>
                            <a href="/kullanici-sozlesmesi" target="_blank">Kullanım</a> ve
                            <a href="/gizlilik-politikasi" target="_blank">gizlilik koşullarını</a> kabul ediyorum.
                        </span>
                    </label>
                </div>

                <div class="login-btn-row">
                    <button type="submit" id="kayitol" class="login-btn">Kayıt Ol</button>
                </div>

                <div class="login-divider">veya</div>

                <a href="/login" class="login-btn-secondary">
                    <i class="fa fa-sign-in"></i> Giriş Yap
                </a>
            </form>
        </div>
    </div>
</div>
@endsection
