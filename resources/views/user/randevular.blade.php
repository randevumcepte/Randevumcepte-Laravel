@extends('layout.layout_profil')
@section('content')

@include('partials.customer-nav-pills', ['active' => 'randevularim'])

<section class="profile-main-container">
    <div class="container">
        <div class="profile-card profile-card--list">
            <div class="profile-card-header">
                <h2>
                    <i class="fa fa-calendar-check-o"></i>
                    Randevularım
                </h2>
                <a href="/" class="profile-action-btn">
                    <i class="fa fa-plus"></i>
                    <span>Yeni Randevu Al</span>
                </a>
            </div>
            <div class="profile-card-body">

                @if($randevular->isEmpty())
                <div class="profile-empty">
                    <i class="fa fa-calendar-o"></i>
                    <p><strong>Henüz randevunuz yok</strong></p>
                    <small>Yeni bir randevu oluşturmak için yukarıdaki butonu kullanabilirsiniz.</small>
                </div>
                @endif

                <div class="appointment-grid">
                    @foreach($randevular as $randevuliste)
                    @php
                        $aylar = ['January'=>'Ocak','February'=>'Şubat','Febuary'=>'Şubat','March'=>'Mart','April'=>'Nisan','May'=>'Mayıs','June'=>'Haziran','July'=>'Temmuz','August'=>'Ağustos','September'=>'Eylül','October'=>'Ekim','November'=>'Kasım','December'=>'Aralık'];
                        $gunler = ['Monday'=>'Pazartesi','Tuesday'=>'Salı','Wednesday'=>'Çarşamba','Thursday'=>'Perşembe','Friday'=>'Cuma','Saturday'=>'Cumartesi','Sunday'=>'Pazar'];
                        $tarih = strtotime($randevuliste->tarih);
                        $gun = strtr(date('l', $tarih), $gunler);
                        $ay = strtr(date('F', $tarih), $aylar);
                        $salonAdi = \App\Salonlar::where('id',$randevuliste->salon_id)->value('salon_adi');
                        $hizmetler = \App\RandevuHizmetler::where('randevu_id',$randevuliste->id)->get();
                    @endphp

                    <div class="appointment-card durum-{{ $randevuliste->durum }}">
                        <input type="hidden" name="randevuno" value="{{$randevuliste->id}}">
                        <input type="hidden" name="salonno" data-value="{{$randevuliste->id}}" value="{{$randevuliste->salon_id}}">

                        <div class="appointment-date">
                            <span class="apt-day">{{ date('d', $tarih) }}</span>
                            <span class="apt-month">{{ $ay }}</span>
                            <span class="apt-time">{{ date('H:i', strtotime($randevuliste->saat)) }}</span>
                            <span class="apt-weekday">{{ $gun }}</span>
                        </div>

                        <div class="appointment-body">
                            <div class="appointment-salon">
                                <i class="fa fa-store" style="color:var(--primary-purple);"></i>
                                <span name="salonadi" data-value="{{$randevuliste->id}}">{{ $salonAdi }}</span>
                            </div>

                            @if(count($hizmetler) > 0)
                            <div class="appointment-row">
                                <span class="apt-label"><i class="fa fa-list-ul"></i> Hizmetler:</span>
                                <span class="apt-value">
                                    @foreach($hizmetler as $rh)
                                        <span class="apt-chip">{{ \App\Hizmetler::where('id',$rh->hizmet_id)->value('hizmet_adi') }}</span>
                                    @endforeach
                                </span>
                            </div>
                            <div class="appointment-row">
                                <span class="apt-label"><i class="fa fa-user-md"></i> Personel:</span>
                                <span class="apt-value">
                                    @foreach($hizmetler as $rh)
                                        <span class="apt-chip apt-chip--soft">{{ \App\Personeller::where('id',$rh->personel_id)->value('personel_adi') }}</span>
                                    @endforeach
                                </span>
                            </div>
                            @endif

                            <div class="appointment-meta">
                                <span class="apt-meta-item"><i class="fa fa-clock-o"></i> Oluşturulma: {{ date('d.m.Y', strtotime($randevuliste->created_at)) }}</span>
                            </div>
                        </div>

                        <div class="appointment-side">
                            @if($randevuliste->durum == 0)
                                <span name="randevudurum" data-value="{{$randevuliste->id}}" class="apt-status apt-status--bekleme">
                                    <i class="fa fa-hourglass-half"></i> Beklemede
                                </span>
                                <button class="apt-action apt-action--danger" data-value="{{$randevuliste->id}}" name="randevuiptalet" type="button">
                                    <i class="fa fa-times"></i> İptal Et
                                </button>
                            @elseif($randevuliste->durum == 1)
                                <span name="randevudurum" data-value="{{$randevuliste->id}}" class="apt-status apt-status--onay">
                                    <i class="fa fa-check-circle"></i> Onaylandı
                                </span>
                                @if(\App\SalonPuanlar::where('user_id',Auth::user()->id)->where('salon_id',$randevuliste->salon_id)->count()==0)
                                <a href="/##yorumtext_yorum" class="apt-action apt-action--info" name="puanyorumla" data-value="{{$randevuliste->id}}">
                                    <i class="fa fa-star"></i> Puanla & Yorumla
                                </a>
                                @endif
                            @elseif($randevuliste->durum == 2)
                                <span name="randevudurum" data-value="{{$randevuliste->id}}" class="apt-status apt-status--iptal">
                                    <i class="fa fa-ban"></i> İptal Edildi
                                </span>
                            @elseif($randevuliste->durum == 3)
                                <span name="randevudurum" class="apt-status apt-status--iptal-self">
                                    <i class="fa fa-times-circle"></i> İptal Ettiniz
                                </span>
                            @endif
                            <span name="guncelrandevudurum" data-value="{{$randevuliste->id}}"></span>
                        </div>
                    </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
</section>

@endsection
