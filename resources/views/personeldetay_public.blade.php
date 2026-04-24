@extends('layout.layout_salondetay')
@section('content')
@php
  $geriLink = url('/'.str_slug($salon->salon_adi).'-'.$salon->id);
@endphp
<style>
  .perdet-hero{background:linear-gradient(135deg,#f5f7fa 0%,#e9eef5 100%); padding:40px 0 30px}
  .perdet-breadcrumb{font-size:13px; color:#666; margin-bottom:16px}
  .perdet-breadcrumb a{color:#666; text-decoration:none}
  .perdet-breadcrumb a:hover{color:#007bff}
  .perdet-card{background:#fff; border-radius:16px; box-shadow:0 8px 24px rgba(0,0,0,.06); overflow:hidden; display:grid; grid-template-columns:280px 1fr; gap:0}
  @media (max-width:768px){ .perdet-card{grid-template-columns:1fr} }
  .perdet-avatar-wrap{background:#f8f9fb; padding:30px; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center}
  .perdet-avatar-wrap img{width:200px; height:200px; border-radius:50%; object-fit:cover; border:6px solid #fff; box-shadow:0 4px 14px rgba(0,0,0,.1)}
  .perdet-body{padding:30px}
  .perdet-name{font-size:28px; font-weight:700; margin:0 0 4px; color:#1a2b4a}
  .perdet-title{font-size:15px; color:#007bff; font-weight:600; margin-bottom:14px}
  .perdet-badges{display:flex; flex-wrap:wrap; gap:8px; margin-bottom:18px}
  .perdet-badge{display:inline-flex; align-items:center; gap:6px; padding:6px 12px; background:#f0f4f9; color:#334; border-radius:20px; font-size:13px; text-decoration:none}
  .perdet-badge i{color:#007bff}
  .perdet-badge.ig{background:linear-gradient(45deg,#f09433,#dc2743 50%,#bc1888); color:#fff}
  .perdet-badge.ig i{color:#fff}
  .perdet-bio{font-size:15px; line-height:1.7; color:#3a4a5e; background:#fafbfc; padding:18px 20px; border-left:3px solid #007bff; border-radius:0 8px 8px 0}
  .perdet-section{margin-top:40px}
  .perdet-section h3{font-size:20px; font-weight:700; color:#1a2b4a; margin-bottom:18px; padding-bottom:10px; border-bottom:2px solid #e9ecef}
  .perdet-hizmetler{display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:12px}
  .perdet-hizmet{background:#fff; padding:14px 16px; border:1px solid #e9ecef; border-radius:10px; display:flex; align-items:center; gap:10px; transition:all .2s}
  .perdet-hizmet:hover{border-color:#007bff; box-shadow:0 4px 12px rgba(0,123,255,.12)}
  .perdet-hizmet i{color:#007bff; font-size:18px}
  .perdet-digerleri{display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:16px}
  .perdet-diger{background:#fff; border:1px solid #e9ecef; border-radius:12px; padding:16px; text-align:center; text-decoration:none; color:#333; transition:all .2s}
  .perdet-diger:hover{border-color:#007bff; transform:translateY(-3px); box-shadow:0 6px 16px rgba(0,123,255,.12); color:#333; text-decoration:none}
  .perdet-diger img{width:72px; height:72px; border-radius:50%; object-fit:cover; margin:0 auto 10px; display:block}
  .perdet-diger .ad{font-weight:600; font-size:14px}
  .perdet-diger .uzmanlik{font-size:11px; color:#888; margin-top:3px}
  .perdet-cta{margin-top:30px; text-align:center}
  .perdet-cta .btn{padding:12px 28px; font-weight:600; font-size:15px}
</style>

<section class="perdet-hero">
  <div class="container">
    <div class="perdet-breadcrumb">
      <a href="{{$geriLink}}"><i class="fa fa-chevron-left"></i> {{$salon->salon_adi}}</a> / <span>{{$adSoyad}}</span>
    </div>

    <div class="perdet-card">
      <div class="perdet-avatar-wrap">
        <img src="{{secure_asset($profilResim)}}" alt="{{$adSoyad}}">
      </div>
      <div class="perdet-body">
        <h1 class="perdet-name">{{$adSoyad}}</h1>
        @if(!empty($personel->uzmanlik))
          <div class="perdet-title">{{$personel->uzmanlik}}</div>
        @elseif(!empty($personel->unvan))
          <div class="perdet-title">{{$personel->unvan}}</div>
        @endif

        <div class="perdet-badges">
          @if(!empty($personel->yillik_tecrube))
            <span class="perdet-badge"><i class="fa fa-star"></i> {{$personel->yillik_tecrube}}+ yıl tecrübe</span>
          @endif
          @if(!empty($personel->unvan) && !empty($personel->uzmanlik))
            <span class="perdet-badge"><i class="fa fa-user"></i> {{$personel->unvan}}</span>
          @endif
          @if(!empty($personel->instagram))
            <a class="perdet-badge ig" href="https://instagram.com/{{ltrim($personel->instagram,'@')}}" target="_blank" rel="noopener">
              <i class="fa fa-instagram"></i> @{{ltrim($personel->instagram,'@')}}
            </a>
          @endif
        </div>

        @if(!empty($personel->aciklama))
          <div class="perdet-bio">{!! nl2br(e($personel->aciklama)) !!}</div>
        @else
          <div class="perdet-bio" style="color:#888; font-style:italic; border-left-color:#ccc">
            Bu personel için henüz detaylı açıklama eklenmemiş.
          </div>
        @endif

        <div class="perdet-cta">
          <a href="{{$geriLink}}#randevubolumu" class="btn btn-primary"><i class="fa fa-calendar-plus-o"></i> {{$adSoyad}} ile Randevu Al</a>
        </div>
      </div>
    </div>

    @if($sunulanHizmetler && $sunulanHizmetler->count() > 0)
    <div class="perdet-section">
      <h3><i class="fa fa-check-circle"></i> Sunduğu Hizmetler</h3>
      <div class="perdet-hizmetler">
        @foreach($sunulanHizmetler as $h)
          <div class="perdet-hizmet">
            <i class="fa fa-check"></i>
            <span>{{$h->hizmet_adi ?? ($h->ad ?? '-')}}</span>
          </div>
        @endforeach
      </div>
    </div>
    @endif

    @if($digerPersoneller && $digerPersoneller->count() > 0)
    <div class="perdet-section">
      <h3><i class="fa fa-users"></i> Ekibimizden Diğer Kişiler</h3>
      <div class="perdet-digerleri">
        @foreach($digerPersoneller as $dp)
          @php
            $dpYetkili = \App\IsletmeYetkilileri::where('personel_id',$dp->id)->first();
            $dpResim = $dpYetkili ? $dpYetkili->profil_resim : null;
            if(empty($dpResim)) $dpResim = $dp->cinsiyet==0 ? 'public/img/author0.jpg' : 'public/img/author1.jpg';
            $dpAd = $dpYetkili && $dpYetkili->name ? $dpYetkili->name : $dp->personel_adi;
          @endphp
          <a class="perdet-diger" href="{{url('/'.str_slug($salon->salon_adi).'-'.$salon->id.'/personel/'.$dp->id)}}">
            <img src="{{secure_asset($dpResim)}}" alt="{{$dpAd}}">
            <div class="ad">{{$dpAd}}</div>
            @if(!empty($dp->uzmanlik))
              <div class="uzmanlik">{{$dp->uzmanlik}}</div>
            @elseif(!empty($dp->unvan))
              <div class="uzmanlik">{{$dp->unvan}}</div>
            @endif
          </a>
        @endforeach
      </div>
    </div>
    @endif
  </div>
</section>
@endsection
