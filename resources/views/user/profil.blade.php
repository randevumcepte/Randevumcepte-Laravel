@extends('layout.layout_profil')
@section('content')

<!-- Modern Navigation Pills -->
<div class="modern-nav-pills">
    <a href="/profilim" class="nav-pill-item active">
        <i class="fa fa-user"></i>
        <span>Profilim</span>
    </a>
    <a href="/randevularim" class="nav-pill-item">
        <i class="fa fa-calendar"></i>
        <span>Randevularım</span>
    </a>
    <a href="/ayarlarim" class="nav-pill-item">
        <i class="fa fa-cog"></i>
        <span>Ayarlarım</span>
    </a>
</div>

<!-- Profile Main Container -->
<section class="profile-main-container">
    <div class="container">
        <form class="form" enctype="multipart/form-data" method="POST" value="{{ csrf_token() }}" action="{{route('musteri_profil_guncelleme')}}">
            {{ csrf_field() }}

            <div class="row">
                <!-- Left Column - Form -->
                <div class="col-lg-8 col-md-7">
                    <div class="profile-card">
                        <div class="profile-card-header">
                            <h2>
                                <i class="fa fa-id-card"></i>
                                Profil Bilgilerim
                            </h2>
                        </div>
                        <div class="profile-card-body">
                            <div class="profile-form-row">
                                <div class="modern-form-group">
                                    <label for="name" class="required">
                                        <i class="fa fa-user"></i>
                                        Ad Soyad
                                    </label>
                                    <input
                                        name="name"
                                        type="text"
                                        class="modern-form-control"
                                        id="name"
                                        placeholder="Adınız ve Soyadınız"
                                        value="{{Auth::user()->name}}"
                                        required>
                                </div>

                                <div class="modern-form-group">
                                    <label for="cep_telofon" class="required">
                                        <i class="fa fa-mobile"></i>
                                        Cep Telefonu
                                    </label>
                                    <input
                                        name="cep_telofon"
                                        type="number"
                                        class="modern-form-control"
                                        id="cep_telofon"
                                        placeholder="5XX XXX XX XX"
                                        value="{{Auth::user()->cep_telefon}}"
                                        required>
                                </div>
                            </div>

                            <div class="profile-form-row">
                                <div class="modern-form-group">
                                    <label for="email" class="required">
                                        <i class="fa fa-envelope"></i>
                                        E-posta
                                    </label>
                                    <input
                                        name="email"
                                        type="email"
                                        class="modern-form-control"
                                        id="email"
                                        placeholder="ornek@email.com"
                                        value="{{Auth::user()->email}}"
                                        required>
                                </div>

                                <div class="modern-form-group">
                                    <label for="ev_telofon">
                                        <i class="fa fa-phone"></i>
                                        Ev Telefonu
                                    </label>
                                    <input
                                        name="ev_telofon"
                                        type="number"
                                        class="modern-form-control"
                                        id="ev_telofon"
                                        placeholder="0XXX XXX XX XX"
                                        value="{{Auth::user()->ev_telefon}}">
                                </div>
                            </div>

                            <div class="profile-form-row">
                                <div class="modern-form-group">
                                    <label for="dogum_tarihi">
                                        <i class="fa fa-birthday-cake"></i>
                                        Doğum Tarihi
                                    </label>
                                    <input
                                        name="dogum_tarihi"
                                        type="date"
                                        class="modern-form-control"
                                        id="dogum_tarihi"
                                        value="{{Auth::user()->dogum_tarihi}}">
                                </div>

                                <div class="modern-form-group">
                                    <label for="cinsiyet">
                                        <i class="fa fa-venus-mars"></i>
                                        Cinsiyet
                                    </label>
                                    <select name="cinsiyet" class="modern-form-control" id="cinsiyet">
                                        @if(Auth::user()->cinsiyet==1)
                                            <option value="0">Kadın</option>
                                            <option value="1" selected="true">Erkek</option>
                                        @else
                                            <option value="0" selected="true">Kadın</option>
                                            <option value="1">Erkek</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Profile Image -->
                <div class="col-lg-4 col-md-5">
                    <div class="profile-card">
                        <div class="profile-card-header">
                            <h2>
                                <i class="fa fa-camera"></i>
                                Profil Resmi
                            </h2>
                        </div>
                        <div class="profile-card-body">
                            <div class="profile-image-section">
                                <div class="profile-image-wrapper">
                                    <div class="profile-image-container">
                                        @if(Auth::user()->profil_resim != '' && Auth::user()->profil_resim != null)
                                            <img src="{{secure_asset(Auth::user()->profil_resim)}}" alt="Profil Resmi">
                                        @else
                                            <img src="{{secure_asset('public/img/author-09.jpg')}}" alt="Profil Resmi">
                                        @endif
                                    </div>
                                    <div class="profile-image-badge">
                                        <i class="fa fa-star"></i>
                                    </div>
                                </div>

                                <div class="profile-image-actions">
                                    <div class="file-input-wrapper">
                                        <input type="file" id="profil_resim" name="profil_resim" accept="image/*">
                                        <button type="button" class="btn-upload">
                                            <i class="fa fa-upload"></i>
                                            <span>Resim Yükle</span>
                                        </button>
                                    </div>
                                    <a href="{{route('musteri_profil_resmi_kaldirma')}}" class="btn-delete">
                                        <i class="fa fa-trash"></i>
                                        <span>Resmi Sil</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="row">
                <div class="col-12">
                    <div class="btn-submit-wrapper">
                        <button id="profilbilgiguncelle" type="submit" class="btn-submit">
                            <span>Bilgileri Güncelle</span>
                            <i class="fa fa-check"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

@endsection
        