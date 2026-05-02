@extends('layout.layout_profil')
@section('content')

@include('partials.customer-nav-pills', ['active' => 'ayarlarim'])

<section class="profile-main-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-10">
                <div class="profile-card">
                    <div class="profile-card-header">
                        <h2>
                            <i class="fa fa-lock"></i>
                            Şifre Değiştir
                        </h2>
                    </div>
                    <div class="profile-card-body">
                        <p class="profile-card-help">
                            Hesabınızın güvenliği için lütfen güçlü bir şifre seçin. Mevcut şifrenizi girip yeni şifrenizi belirleyebilirsiniz.
                        </p>

                        <form method="POST" id="musteri_sifre_degistir" action="{{ route('sifredegistir') }}">
                            {{ csrf_field() }}

                            <div class="modern-form-group">
                                <label for="current-password" class="required">
                                    <i class="fa fa-key"></i>
                                    Mevcut Şifreniz
                                </label>
                                <input id="current-password" type="password" class="modern-form-control"
                                       name="current-password" placeholder="••••••••" required>
                                @if ($errors->has('current-password'))
                                    <span class="modern-form-error">{{ $errors->first('current-password') }}</span>
                                @endif
                            </div>

                            <div class="modern-form-group">
                                <label for="new-password" class="required">
                                    <i class="fa fa-lock"></i>
                                    Yeni Şifre
                                </label>
                                <input id="new-password" type="password" class="modern-form-control"
                                       name="new-password" placeholder="En az 6 karakter" required>
                                @if ($errors->has('new-password'))
                                    <span class="modern-form-error">{{ $errors->first('new-password') }}</span>
                                @endif
                            </div>

                            <div class="modern-form-group">
                                <label for="new-password-confirm" class="required">
                                    <i class="fa fa-check-circle"></i>
                                    Yeni Şifre (Tekrar)
                                </label>
                                <input id="new-password-confirm" type="password" class="modern-form-control"
                                       name="new-password_confirmation" placeholder="Yeni şifreyi tekrar girin" required>
                            </div>

                            <div class="btn-submit-wrapper" style="margin-top: 24px;">
                                <button type="submit" class="btn-submit">
                                    <span>Şifreyi Değiştir</span>
                                    <i class="fa fa-check"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
