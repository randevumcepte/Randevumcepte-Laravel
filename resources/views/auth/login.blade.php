@extends('layout.layout_login')

@section('content')
<div class="login-wrapper">
    <div class="login-container">
        <!-- Left Side - Hero -->
        <div class="login-header">
            <video class="login-video-bg" autoplay muted loop playsinline preload="auto">
                <source src="{{secure_asset('public/videos/login-bg.m4v')}}" type="video/mp4">
            </video>

            <img src="{{secure_asset(\App\Salonlar::where('domain',$_SERVER['HTTP_HOST'])->value('logo'))}}" alt="{{\App\Salonlar::where('domain',$_SERVER['HTTP_HOST'])->value('salon_adi')}}" class="login-logo">

            <div class="login-hero-text">
                <h1 class="login-title">Hoş<br>Geldiniz.</h1>
                <p class="login-subtitle">{{\App\Salonlar::where('domain',$_SERVER['HTTP_HOST'])->value('salon_adi')}} randevu sistemine giriş yaparak randevularınızı yönetebilir, kampanyaları takip edebilir ve hizmetlerimizden hızlıca faydalanabilirsiniz.</p>
            </div>

            <div class="login-social">
                <p class="login-social-label">Hızlı Bağlantılar</p>
                <div class="login-social-buttons">
                    <a href="/register" class="login-social-btn">
                        <i class="fa fa-user-plus"></i> Üye Ol
                    </a>
                    <a href="/isletmeyonetim/girisyap" class="login-social-btn">
                        <i class="fa fa-briefcase"></i> İşletme Girişi
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Side - Form -->
        <div class="login-body">
            <h2 class="login-body-title">Giriş Yap</h2>
            <p class="login-body-subtitle">Hesabınız yok mu? <a href="/register">Hemen kayıt olun</a>, bir dakikadan kısa sürer.</p>

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
            </form>
        </div>
    </div>
</div>
@endsection
