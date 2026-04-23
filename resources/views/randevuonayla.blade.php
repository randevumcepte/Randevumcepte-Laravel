@extends('layout.layout_randevuonayla')
@section('content')

<div class="rdv-booking-v2">
    <div id="preloader"></div>

    <div class="rdv-confirm-wrap">
        <div class="rdv-confirm-card">
            <div class="rdv-confirm-header">
                <div class="rdv-confirm-header__icon">
                    <i class="fa fa-calendar-check-o"></i>
                </div>
                <h1>Randevu Onayı</h1>
                <p>Aşağıdaki bilgileri kontrol ederek randevunuzu tamamlayın.</p>
            </div>

            <form id="randevuonayformu" method="POST">
                {!! csrf_field() !!}

                <div class="rdv-confirm-body">
                    {{-- Salon --}}
                    <div class="rdv-confirm-row">
                        <div class="rdv-confirm-row__icon"><i class="fa fa-building-o"></i></div>
                        <div class="rdv-confirm-row__label">Salon adı</div>
                        <div class="rdv-confirm-row__value">
                            <input type="hidden" name="salonno" value="{{$salon->id}}">
                            {{$salon->salon_adi}}
                        </div>
                    </div>

                    {{-- Services --}}
                    <div class="rdv-confirm-row">
                        <div class="rdv-confirm-row__icon"><i class="fa fa-list-alt"></i></div>
                        <div class="rdv-confirm-row__label">Seçilen hizmetler</div>
                        <div class="rdv-confirm-row__value">
                            @foreach($secilenhizmetler as $key => $value)
                                <input type="hidden" name="hizmetler[]" value="{{$value->id}}">
                                {{$value->hizmet_adi}}@if($key+1 != $secilenhizmetler->count()), @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Personnel --}}
                    <div class="rdv-confirm-row">
                        <div class="rdv-confirm-row__icon"><i class="fa fa-users"></i></div>
                        <div class="rdv-confirm-row__label">Personeller</div>
                        <div class="rdv-confirm-row__value">
                            <?php $personelparametre = explode('_',$personelparametre); ?>
                            <ul class="rdv-personel-list">
                                @foreach($personelparametre as $personelparametre1)
                                    @if($personelparametre1 != null || $personelparametre1 != '')
                                        <li class="rdv-personel-chip">
                                            <input type="hidden" name="personeller[]" value="{{\App\Personeller::where('id',$personelparametre1)->value('id')}}">
                                            @if(\App\Personeller::where('id',$personelparametre1)->value('profil_resmi') == '' || \App\Personeller::where('id',$personelparametre1)->value('profil_resmi') == null)
                                                @if(\App\Personeller::where('id',$personelparametre1)->value('cinsiyet')==0)
                                                    <img src="{{secure_asset('public/img/author0.jpg')}}" alt="Profil Resmi" />
                                                @else
                                                    <img src="{{secure_asset('public/img/author1.jpg')}}" alt="Profil Resmi" />
                                                @endif
                                            @else
                                                <img src="{{secure_asset(\App\Personeller::where('id',$personelparametre1)->value('profil_resmi'))}}" alt="Profil Resmi" />
                                            @endif
                                            <span>{{\App\Personeller::where('id',$personelparametre1)->value('personel_adi')}}</span>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    {{-- Date --}}
                    <div class="rdv-confirm-row">
                        <div class="rdv-confirm-row__icon"><i class="fa fa-calendar"></i></div>
                        <div class="rdv-confirm-row__label">Randevu tarihi</div>
                        <div class="rdv-confirm-row__value">
                            <input type="hidden" name="randevutarihi" value="{{$randevutarihi}}">
                            {{$randevutarihi}}
                        </div>
                    </div>

                    {{-- Time --}}
                    <div class="rdv-confirm-row">
                        <div class="rdv-confirm-row__icon"><i class="fa fa-clock-o"></i></div>
                        <div class="rdv-confirm-row__label">Randevu saati</div>
                        <div class="rdv-confirm-row__value">
                            <input type="hidden" name="randevusaati" value="{{str_replace('_',':',$randevusaati)}}">
                            {{$randevusaati}}
                        </div>
                    </div>
                </div>

                <div class="rdv-confirm-footer">
                    <p class="rdv-confirm-footer__text">
                        <i class="fa fa-info-circle" style="color:#5C008E"></i>
                        Randevu aldığınızda <a href="/kullanici-sozlesmesi" style="color:#5C008E;font-weight:500">kullanım</a> ve
                        <a href="/gizlilik-politikasi" style="color:#5C008E;font-weight:500">gizlilik</a> koşullarını kabul etmiş sayılırsınız.
                    </p>

                    <p class="rdv-confirm-footer__text" style="font-weight:500;color:#1F2937;margin-bottom:16px">
                        Yukarıda detayları listelenen randevunuzu onaylamak istiyor musunuz?
                    </p>

                    <div class="rdv-confirm-actions">
                        <button type="button" class="rdv-btn rdv-btn--outline">
                            <i class="fa fa-times"></i> Hayır
                        </button>
                        <button type="button" id="randevuonaylabutton" class="rdv-btn rdv-btn--success">
                            <i class="fa fa-check"></i> Evet, Onayla
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div id="randevuonaybildirim"></div>

        {{-- Preserve legacy table structure for any JS that might query it --}}
        <table class="randevuozet" style="display:none">
            <tr><td></td><td></td></tr>
        </table>
    </div>
</div>

@endsection
