@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
{!!csrf_field()!!}

<style>
   /* === Modern Tahsilat Tasarımı === */
   .tahsilat-modern *{ box-sizing:border-box; }
   .tahsilat-modern{ font-family:'Inter','Segoe UI',sans-serif; }

   .tm-header{
      background: linear-gradient(135deg,#5C008E 0%,#7B2FB8 50%,#9D5DC8 100%);
      color:#fff; border-radius:14px; padding:18px 22px; margin-bottom:18px;
      box-shadow:0 8px 24px -8px rgba(92,0,142,.35);
      display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;
   }
   .tm-header h1{ margin:0; font-size:20px; font-weight:700; color:#fff; }
   .tm-header .tm-sub{ opacity:.85; font-size:13px; margin-top:4px; }
   .tm-header .tm-actions a, .tm-header .tm-actions button{ font-weight:600; }
   .tm-badge{
      display:inline-flex; align-items:center; gap:6px;
      background:rgba(255,255,255,.15); padding:4px 10px; border-radius:20px;
      font-size:12px; font-weight:600; backdrop-filter: blur(6px);
   }
   .tm-back-link{
      color:#fff; text-decoration:none; padding:6px 12px; border-radius:8px;
      background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.25); font-size:13px;
   }
   .tm-back-link:hover{ background:rgba(255,255,255,.22); color:#fff; }

   .tm-card{
      background:#fff; border-radius:14px; padding:18px;
      box-shadow:0 4px 14px -6px rgba(31,41,55,.12);
      border:1px solid #eef0f4; margin-bottom:16px;
   }
   .tm-card-title{
      font-size:14px; font-weight:700; color:#475569; text-transform:uppercase;
      letter-spacing:.5px; margin-bottom:12px;
      display:flex; align-items:center; gap:8px;
   }
   .tm-card-title i{ color:#7B2FB8; }

   /* Item buttons */
   .tm-item-buttons{ display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px; }
   .tm-item-buttons .btn{ flex:1; min-width:120px; font-weight:600; border-radius:10px; padding:12px 14px; }
   .tm-action-buttons{ display:flex; gap:10px; flex-wrap:wrap; margin-bottom:14px; }
   .tm-action-buttons .btn{ font-weight:600; border-radius:10px; padding:10px 16px; }

   /* Items list */
   .tm-item-row{
      background:#f8fafc; border:1px solid #e2e8f0; border-left:4px solid #7B2FB8;
      border-radius:10px; padding:12px 14px; margin-bottom:8px;
      display:flex; align-items:center; flex-wrap:wrap; gap:10px;
      transition:all .15s ease;
   }
   .tm-item-row:hover{ background:#f1f5f9; border-left-color:#5C008E; }
   .tm-item-row.taksit-row{ border-left-color:#0ea5e9; background:#eff6ff; }
   .tm-item-row.senet-row{ border-left-color:#f59e0b; background:#fffbeb; }
   .tm-item-name{ flex:1 1 200px; font-weight:600; color:#1e293b; font-size:14px; }
   .tm-item-meta{ flex:1 1 130px; color:#64748b; font-size:13px; }
   .tm-item-qty{ flex:0 0 130px; }
   .tm-item-qty input{ height:34px; font-size:13px; }
   .tm-item-amount{ flex:0 0 200px; display:flex; align-items:center; justify-content:flex-end; gap:8px; }
   .tm-item-amount input{ height:34px; font-size:14px; font-weight:600; text-align:right; max-width:130px; }
   .tm-item-actions{ flex:0 0 36px; }
   .tm-empty{
      text-align:center; padding:30px 20px; color:#94a3b8;
      background:#f8fafc; border:2px dashed #e2e8f0; border-radius:12px;
   }
   .tm-empty i{ font-size:38px; opacity:.4; }

   /* Form fields */
   .tm-field{ margin-bottom:14px; }
   .tm-field label{
      display:block; font-size:12px; font-weight:600; color:#64748b;
      text-transform:uppercase; letter-spacing:.4px; margin-bottom:5px;
   }
   .tm-field input, .tm-field select{
      width:100%; height:42px; padding:0 12px; border:1px solid #e2e8f0;
      border-radius:10px; font-size:14px; background:#fff;
   }
   .tm-field input:focus, .tm-field select:focus{
      border-color:#7B2FB8; outline:none; box-shadow:0 0 0 3px rgba(123,47,184,.12);
   }
   .tm-field input.tm-highlight{
      background:#ecfdf5; border-color:#10b981; font-size:18px; font-weight:700; color:#065f46;
   }
   .tm-field input.tm-readonly{ background:#f1f5f9; color:#475569; }

   /* Submit */
   .tm-submit-btn{
      width:100%; height:54px; font-size:16px; font-weight:700;
      background:linear-gradient(135deg,#10b981,#059669); border:none; color:#fff;
      border-radius:12px; box-shadow:0 6px 14px -4px rgba(16,185,129,.45);
      transition:all .15s ease;
   }
   .tm-submit-btn:hover{ transform:translateY(-1px); box-shadow:0 10px 20px -4px rgba(16,185,129,.55); color:#fff; }

   /* Right summary */
   .tm-summary{
      background:linear-gradient(180deg,#fff,#fafbff);
      border:1px solid #eef0f4; border-radius:14px; padding:20px;
      box-shadow:0 6px 18px -8px rgba(31,41,55,.14); position:sticky; top:14px;
   }
   .tm-summary-title{ font-size:13px; font-weight:700; color:#64748b; letter-spacing:.5px; text-transform:uppercase; margin-bottom:6px; }
   .tm-balance-amount{
      font-size:38px; font-weight:800; color:#dc2626; margin:0 0 16px 0; line-height:1.1;
   }
   .tm-balance-amount.zero{ color:#10b981; }
   .tm-summary-row{
      display:flex; justify-content:space-between; padding:8px 0;
      border-bottom:1px dashed #e2e8f0; font-size:13px; color:#475569;
   }
   .tm-summary-row.total{ font-weight:700; color:#dc2626; font-size:15px; padding-top:12px; border-bottom:none; }
   .tm-summary-row.paid{ color:#059669; font-weight:700; }

   .tm-history{ margin-top:18px; }
   .tm-history-title{ font-size:13px; font-weight:700; color:#475569; margin-bottom:8px; }
   .tm-history-row{
      display:flex; align-items:center; gap:8px; padding:8px 10px;
      background:#f8fafc; border-radius:8px; margin-bottom:6px; font-size:12.5px;
   }
   .tm-history-row .h-date{ color:#64748b; min-width:74px; }
   .tm-history-row .h-amount{ font-weight:700; color:#1e293b; flex:1; }
   .tm-history-row .h-method{ color:#7B2FB8; font-size:11px; background:#f3e8ff; padding:2px 8px; border-radius:8px; }
   .tm-history-row .h-del{
      background:transparent; border:none; color:#dc2626; padding:2px 6px; cursor:pointer; line-height:1;
   }

   /* Status pill */
   .tm-status-pill{
      display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:20px;
      font-size:11px; font-weight:700; letter-spacing:.3px;
   }
   .tm-status-pill.aktif{ background:#dbeafe; color:#1e40af; }
   .tm-status-pill.sadik{ background:#fef3c7; color:#92400e; }
   .tm-status-pill.pasif{ background:#f1f5f9; color:#475569; }

   @media (max-width: 992px){
      .tm-summary{ position:static; }
      .tm-balance-amount{ font-size:30px; }
   }
</style>

<div class="tahsilat-modern">

@php
   $musteri_adisyon_count = \App\Adisyonlar::where('user_id',$musteri->id)->where('salon_id',$isletme->id)->count();
   $sadikSart = $musteri_adisyon_count>3 && date('Y-m-d H:i:s', strtotime('+90 days',strtotime(\App\Adisyonlar::where('user_id',$musteri->id)->where('salon_id',$isletme->id)->orderBy('id','desc')->value('created_at')))) < date('Y-m-d H:i:s', strtotime('+90 days', strtotime(date('Y-m-d H:i:s'))));
   if($sadikSart){ $musteriTipi = 'sadik'; $musteriEtiket = 'Sadık Müşteri'; $musteriIndirimYuzde = $isletme->sadik_musteri_indirim_yuzde; }
   elseif($musteri_adisyon_count==0){ $musteriTipi='pasif'; $musteriEtiket='Pasif Müşteri'; $musteriIndirimYuzde=$isletme->pasif_musteri_indirim_yuzde; }
   else{ $musteriTipi='aktif'; $musteriEtiket='Aktif Müşteri'; $musteriIndirimYuzde=$isletme->aktif_musteri_indirim_yuzde; }
   $adisyonTarih = ($adisyon && $adisyon->tarih) ? date('Y-m-d', strtotime($adisyon->tarih)) : '';
   $adisyonTarihGoster = $adisyonTarih ? date('d.m.Y', strtotime($adisyonTarih)) : '—';
@endphp

<!-- Modern header with gradient -->
<div class="tm-header">
   <div>
      <span class="tm-badge"><i class="fa fa-flask"></i> Modern Tahsilat (Beta)</span>
      <h1 style="margin-top:8px;">{{ $musteri->name }}</h1>
      <div class="tm-sub">
         #{{ $adisyon_id }} nolu satış &nbsp;·&nbsp;
         <span class="tm-status-pill {{ $musteriTipi }}">
            <i class="fa fa-user"></i> {{ $musteriEtiket }}
         </span>
         &nbsp;·&nbsp; <i class="fa fa-percent"></i> %{{ $musteriIndirimYuzde }} müşteri indirimi
      </div>
      <div style="margin-top:10px; display:inline-flex; align-items:center; gap:8px; background:rgba(255,255,255,.12); padding:6px 12px; border-radius:10px; border:1px solid rgba(255,255,255,.2);">
         <i class="fa fa-calendar"></i>
         <span style="font-size:12px; opacity:.85;">Satış Tarihi:</span>
         <strong id="tm-satis-tarihi-goster">{{$adisyonTarihGoster}}</strong>
         <button type="button" id="tm-satis-tarihi-duzenle"
            style="background:rgba(255,255,255,.2); border:none; color:#fff; padding:3px 9px; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer;">
            <i class="fa fa-pencil"></i> Düzenle
         </button>
         <input type="date" id="tm-satis-tarihi-input"
            value="{{$adisyonTarih}}"
            style="display:none; height:28px; border-radius:6px; border:none; padding:0 8px; font-size:13px; color:#1e293b;">
         <button type="button" id="tm-satis-tarihi-kaydet"
            style="display:none; background:#10b981; border:none; color:#fff; padding:3px 9px; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer;">
            <i class="fa fa-check"></i> Kaydet
         </button>
         <button type="button" id="tm-satis-tarihi-iptal"
            style="display:none; background:rgba(255,255,255,.2); border:none; color:#fff; padding:3px 9px; border-radius:6px; font-size:11px; cursor:pointer;">
            İptal
         </button>
      </div>
   </div>
   <div class="tm-actions">
      <a href="/isletmeyonetim/tahsilat/{{$musteri->id}}/{{$adisyon_id}}{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="tm-back-link" title="Eski tasarıma geç">
         <i class="fa fa-arrow-left"></i> Eski Ekran
      </a>
   </div>
</div>

<script>
   document.addEventListener('DOMContentLoaded', function(){
      var goster = document.getElementById('tm-satis-tarihi-goster');
      var btnDuzenle = document.getElementById('tm-satis-tarihi-duzenle');
      var input = document.getElementById('tm-satis-tarihi-input');
      var btnKaydet = document.getElementById('tm-satis-tarihi-kaydet');
      var btnIptal = document.getElementById('tm-satis-tarihi-iptal');
      var orijinalDeger = input ? input.value : '';

      if(!btnDuzenle) return;

      btnDuzenle.addEventListener('click', function(){
         goster.style.display='none';
         btnDuzenle.style.display='none';
         input.style.display='inline-block';
         btnKaydet.style.display='inline-block';
         btnIptal.style.display='inline-block';
         input.focus();
      });

      btnIptal.addEventListener('click', function(){
         input.value = orijinalDeger;
         goster.style.display='inline';
         btnDuzenle.style.display='inline-block';
         input.style.display='none';
         btnKaydet.style.display='none';
         btnIptal.style.display='none';
      });

      btnKaydet.addEventListener('click', function(){
         if(!input.value){ alert('Geçerli bir tarih giriniz'); return; }
         var token = document.querySelector('input[name="_token"]').value;
         btnKaydet.disabled=true; btnKaydet.innerHTML='<i class="fa fa-spinner fa-spin"></i>';
         fetch('/isletmeyonetim/adisyon-tarih-guncelle{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded','X-CSRF-TOKEN':token,'X-Requested-With':'XMLHttpRequest'},
            body:'adisyon_id={{$adisyon_id}}&tarih='+encodeURIComponent(input.value)+'&_token='+encodeURIComponent(token)
         }).then(function(r){return r.json();}).then(function(j){
            btnKaydet.disabled=false; btnKaydet.innerHTML='<i class="fa fa-check"></i> Kaydet';
            if(j.ok){
               goster.textContent = j.tarih_format;
               orijinalDeger = j.tarih;
               input.value = j.tarih;
               goster.style.display='inline';
               btnDuzenle.style.display='inline-block';
               input.style.display='none';
               btnKaydet.style.display='none';
               btnIptal.style.display='none';
            } else {
               alert(j.mesaj || 'Güncelleme başarısız');
            }
         }).catch(function(){
            btnKaydet.disabled=false; btnKaydet.innerHTML='<i class="fa fa-check"></i> Kaydet';
            alert('Bir hata oluştu');
         });
      });
   });
</script>

<form id="adisyon_tahsilat" method="POST">
   <input type="hidden" name="tahsilat_ekrani" id="tahsilat_ekrani" value="1">
   <select style="display:none" name="tahsilat_musteri_id" id='tahsilat_musteri_id'>
      <option selected value="{{$musteri->id}}">777</option>
   </select>
   <input type="hidden" name="tahsilat_tutari" id='toplam_tahsilat_tutari'>
   <input type="hidden" name="sube" value="{{$isletme->id}}">
   <input type="hidden" name="adisyon_id" value="{{$adisyon_id}}">

   <div class="row">
      <!-- Sol kolon: Kalemler + Form -->
      <div class="col-md-8">

         <!-- Item ekleme butonları -->
         <div class="tm-card">
            <div class="tm-card-title"><i class="fa fa-plus-circle"></i> Satışa Kalem Ekle</div>
            <div class="tm-item-buttons">
               <button type="button" data-toggle="modal" data-target="#adisyon_yeni_hizmet_modal" id="adisyon_hizmet_ekle_button" class="btn btn-info adisyon_ekle_buttonlar">
                  <i class="fa fa-cut"></i> Hizmet Ekle
               </button>
               <button type="button" data-toggle="modal" id="adisyon_urun_ekle_button" data-target="#urun_satisi_modal" data-value='' onclick="modalbaslikata('Yeni Ürün Satışı Ekle','')" class="btn btn-danger adisyon_ekle_buttonlar">
                  <i class="fa fa-shopping-bag"></i> Ürün Ekle
               </button>
               <button type="button" data-toggle="modal" id="adisyon_paket_ekle_button" data-target="#paket_satisi_modal" data-value='' class="btn btn-primary adisyon_ekle_buttonlar">
                  <i class="fa fa-cubes"></i> Paket Ekle
               </button>
            </div>
            <div class="tm-action-buttons">
               <button type="button" class="btn btn-success" id='senetle_veya_taksitle_tahsil_et'>
                  <i class="fa fa-list-alt"></i> Alacaklar
               </button>
               <button type="button" id='yeni_taksitli_tahsilat_olusur' href="#" data-value='' class="btn btn-primary">
                  <i class="fa fa-credit-card"></i> Taksit Yap
               </button>
            </div>
         </div>

         <!-- Tahsilat kalemleri -->
         <div class="tm-card">
            <div class="tm-card-title"><i class="fa fa-list"></i> Tahsil Edilecek Kalemler</div>

            <div id='tum_tahsilatlar'>
               @php $kalem_sayisi = 0; @endphp
               @foreach(\App\Adisyonlar::whereIn('id',$acik_adisyonlar->pluck('id'))->get() as $adisyon)

                  @foreach($adisyon->hizmetler as $key=>$hizmet)
                     @if(($hizmet->fiyat - \App\TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar') - $hizmet->indirim_tutari >= 0 || $hizmet->hediye) && $hizmet->senet_id === null && $hizmet->taksitli_tahsilat_id === null)
                     @php $kalem_sayisi++; @endphp
                     <div class="row tahsilat_kalemleri_listesi tm-item-row" data-value="0" style="margin:0 0 8px 0;">
                        <div class="tm-item-name">
                           <i class="fa fa-cut" style="color:#7B2FB8;"></i> {{ ($hizmet->hizmet_id != null ? $hizmet->hizmet->hizmet_adi : '') }}
                        </div>
                        <div class="tm-item-meta">
                           @if($hizmet->personel_id !== null)
                              <i class="fa fa-user-o"></i> {{$hizmet->personel->personel_adi}}
                           @elseif($hizmet->cihaz_id !== null)
                              <i class="fa fa-cog"></i> {{$hizmet->cihaz->cihaz_adi}}
                           @endif
                        </div>
                        <div class="tm-item-qty">
                           <input type="tel" value="{{$hizmet->seans_sayisi}}" data-value="{{$hizmet->id}}" class="form-control" name="hizmet_seans_girilen[]" placeholder="seans">
                        </div>
                        <div class="tm-item-amount">
                           <input type="hidden" name="adisyon_hizmet_id[]" value="{{$hizmet->id}}">
                           <input type="hidden" name="indirim[]" data-value="{{$hizmet->id}}" value="{{$hizmet->indirim_tutari}}">
                           <input type="hidden" name="adisyon_hizmet_senet_id[]" value="{{$hizmet->senet_id}}">
                           <input type="hidden" name="adisyon_hizmet_taksitli_tahsilat_id[]" value="{{$hizmet->taksitli_tahsilat_id}}">
                           <input type="tel" class="form-control try-currency tahsilat_kalemleri" name="himzet_tahsilat_tutari_girilen[]" data-value="{{$hizmet->id}}" value="{{number_format($hizmet->fiyat - \App\TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar') - $hizmet->indirim_tutari,2,',','.')}}">
                           <span style="color:#64748b; font-size:13px;">₺</span>
                           @if($hizmet->hediye)<i class="fa fa-gift" style="color:#f59e0b;"></i>@endif
                        </div>
                        <div class="tm-item-actions">
                           <div class="dropdown">
                              <a class="btn btn-link p-0 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown" style="font-size:18px; color:#64748b;">
                                 <i class="dw dw-more"></i>
                              </a>
                              <div class="dropdown-menu dropdown-menu-right">
                                 @if(($hizmet->senet_id == null && $hizmet->taksitli_tahsilat_id == null && \App\TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->count()==0) || $hizmet->fiyat == 0)
                                    @if(!$hizmet->hediye)
                                       <a class="dropdown-item tahsilat_hizmet_hediye_ver" data-value="{{$hizmet->id}}" href="#"><i class="fa fa-gift"></i> Hediye Ver</a>
                                    @else
                                       <a class="dropdown-item tahsilat_hizmet_hediye_kaldir" data-value="{{$hizmet->id}}" href="#"><i class="fa fa-gift"></i> Hediyeyi Kaldır</a>
                                    @endif
                                    <a class="dropdown-item tahsilat_hizmet_sil" data-value="{{$hizmet->id}}" href="#"><i class="dw dw-delete-3"></i> Sil</a>
                                 @endif
                              </div>
                           </div>
                        </div>
                     </div>
                     @endif
                  @endforeach

                  @foreach($adisyon->urunler as $key=>$urun)
                     @if(($urun->fiyat - \App\TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar') - $urun->indirim_tutari >= 0 || $urun->hediye) && $urun->senet_id === null && $urun->taksitli_tahsilat_id===null)
                     @php $kalem_sayisi++; @endphp
                     <div class="row tahsilat_kalemleri_listesi tm-item-row" data-value="0" style="margin:0 0 8px 0;">
                        <div class="tm-item-name">
                           <i class="fa fa-shopping-bag" style="color:#dc2626;"></i> {{$urun->urun->urun_adi}}
                        </div>
                        <div class="tm-item-meta">
                           @if($urun->personel_id)<i class="fa fa-user-o"></i> {{$urun->personel->personel_adi}}@endif
                        </div>
                        <div class="tm-item-qty">
                           @if(($urun->senet_id == null && $urun->taksitli_tahsilat_id == null && \App\TahsilatUrunler::where('adisyon_urun_id',$urun->id)->count()==0) || $urun->fiyat == 0)
                              <input type="tel" value="{{$urun->adet}}" data-value="{{$urun->id}}" class="form-control" name="urun_adet_girilen[]" placeholder="adet">
                           @else
                              <span style="color:#64748b">{{$urun->adet}} adet</span>
                           @endif
                        </div>
                        <div class="tm-item-amount">
                           <input type="hidden" name="adisyon_urun_id[]" value="{{$urun->id}}">
                           <input type="hidden" name="indirim[]" data-value="{{$urun->id}}" value="{{$urun->indirim_tutari}}">
                           <input type="hidden" name="adisyon_urun_senet_id[]" value="{{$urun->senet_id}}">
                           <input type="hidden" name="adisyon_urun_taksitli_tahsilat_id[]" value="{{$urun->taksitli_tahsilat_id}}">
                           <input type="tel" class="form-control try-currency tahsilat_kalemleri" name="urun_tahsilat_tutari_girilen[]" data-value="{{$urun->id}}" value="{{number_format($urun->fiyat - \App\TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar') - $urun->indirim_tutari,2,',','.')}}">
                           <span style="color:#64748b; font-size:13px;">₺</span>
                           @if($urun->hediye)<i class="fa fa-gift" style="color:#f59e0b;"></i>@endif
                        </div>
                        <div class="tm-item-actions">
                           <div class="dropdown">
                              <a class="btn btn-link p-0 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown" style="font-size:18px; color:#64748b;">
                                 <i class="dw dw-more"></i>
                              </a>
                              <div class="dropdown-menu dropdown-menu-right">
                                 @if(($urun->senet_id == null && $urun->taksitli_tahsilat_id == null && \App\TahsilatUrunler::where('adisyon_urun_id',$urun->id)->count()==0) || $urun->fiyat == 0)
                                    @if(!$urun->hediye)
                                       <a class="dropdown-item tahsilat_urun_hediye_ver" data-value="{{$urun->id}}" href="#"><i class="fa fa-gift"></i> Hediye Ver</a>
                                    @else
                                       <a class="dropdown-item tahsilat_urun_hediye_kaldir" data-value="{{$urun->id}}" href="#"><i class="fa fa-gift"></i> Hediyeyi Kaldır</a>
                                    @endif
                                    <a class="dropdown-item tahsilat_urun_sil" href="#" data-value="{{$urun->id}}"><i class="dw dw-delete-3"></i> Sil</a>
                                 @endif
                              </div>
                           </div>
                        </div>
                     </div>
                     @endif
                  @endforeach

                  @foreach($adisyon->paketler as $key=>$paket)
                     @if(($paket->fiyat - \App\TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar') - $paket->indirim_tutari >= 0 || $paket->hediye) && $paket->senet_id === null && $paket->taksitli_tahsilat_id === null)
                     @php $kalem_sayisi++; @endphp
                     <div class="row tahsilat_kalemleri_listesi tm-item-row" data-value="0" style="margin:0 0 8px 0;">
                        <div class="tm-item-name">
                           <i class="fa fa-cubes" style="color:#0ea5e9;"></i> {{$paket->paket->paket_adi}}
                        </div>
                        <div class="tm-item-meta">
                           @if($paket->personel_id)<i class="fa fa-user-o"></i> {{$paket->personel->personel_adi}}@endif
                        </div>
                        <div class="tm-item-qty">
                           <input type="tel" value="{{$paket->seans_sayisi}}" data-value="{{$paket->id}}" class="form-control" name="paket_seans_girilen[]" placeholder="seans">
                        </div>
                        <div class="tm-item-amount">
                           <input type="hidden" name="adisyon_paket_id[]" value="{{$paket->id}}">
                           <input type="hidden" name="adisyon_paket_senet_id[]" value="{{$paket->senet_id}}">
                           <input type="hidden" name="adisyon_paket_taksitli_tahsilat_id[]" value="{{$paket->taksitli_tahsilat_id}}">
                           <input type="hidden" name="indirim[]" data-value="{{$paket->id}}" value="{{$paket->indirim_tutari}}">
                           <input type="tel" class="form-control try-currency tahsilat_kalemleri" name="paket_tahsilat_tutari_girilen[]" data-value="{{$paket->id}}" value="{{number_format($paket->fiyat - \App\TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar') - $paket->indirim_tutari,2,',','.')}}">
                           <span style="color:#64748b; font-size:13px;">₺</span>
                           @if($paket->hediye)<i class="fa fa-gift" style="color:#f59e0b;"></i>@endif
                        </div>
                        <div class="tm-item-actions">
                           <div class="dropdown">
                              <a class="btn btn-link p-0 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown" style="font-size:18px; color:#64748b;">
                                 <i class="dw dw-more"></i>
                              </a>
                              <div class="dropdown-menu dropdown-menu-right">
                                 @if(($paket->senet_id == null && $paket->taksitli_tahsilat_id == null && \App\TahsilatPaketler::where('adisyon_paket_id',$paket->id)->count()==0) || $paket->fiyat == 0)
                                    @if(!$paket->hediye)
                                       <a class="dropdown-item tahsilat_paket_hediye_ver" data-value="{{$paket->id}}" href="#"><i class="fa fa-gift"></i> Hediye Ver</a>
                                    @else
                                       <a class="dropdown-item tahsilat_paket_hediye_kaldir" data-value="{{$paket->id}}" href="#"><i class="fa fa-gift"></i> Hediyeyi Kaldır</a>
                                    @endif
                                    <a class="dropdown-item tahsilat_paket_sil" data-value="{{$paket->id}}" href="#"><i class="dw dw-delete-3"></i> Sil</a>
                                 @endif
                              </div>
                           </div>
                        </div>
                     </div>
                     @endif
                  @endforeach
               @endforeach
            </div>

            <div id="taksitli_ve_senetli_tahsilatlar">
               @foreach($taksit_gelen_vadeler as $taksit_gelen_vade)
               @php $kalem_sayisi++; @endphp
               <div class="row tahsilat_kalemleri_listesi taksit_vadeleri_listesi tm-item-row taksit-row" data-value="{{$taksit_gelen_vade->taksit_vade_id}}" style="margin:0 0 8px 0;">
                  <div class="tm-item-name"><i class="fa fa-credit-card" style="color:#0ea5e9;"></i> Taksit Vadesi</div>
                  <div class="tm-item-meta"><i class="fa fa-calendar"></i> {{date('d.m.Y', strtotime($taksit_gelen_vade->tarih))}}</div>
                  <div class="tm-item-qty"><span style="color:#64748b">1 adet</span></div>
                  <div class="tm-item-amount">
                     <input type="hidden" name="taksit_vade_id[]" value="{{$taksit_gelen_vade->taksit_vade_id}}">
                     <input type="hidden" class="form-control try-currency tahsilat_kalemleri" name="taksit_tahsilat_tutari_girilen[]" value="{{number_format($taksit_gelen_vade->tutar,2,',','.')}}">
                     <span style="font-weight:700; color:#1e293b;">{{number_format($taksit_gelen_vade->tutar,2,',','.')}} ₺</span>
                  </div>
                  <div class="tm-item-actions">
                     <div class="dropdown">
                        <a class="btn btn-link p-0 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown" style="font-size:18px; color:#64748b;"><i class="dw dw-more"></i></a>
                        <div class="dropdown-menu dropdown-menu-right">
                           <a class="dropdown-item tahsilat_taksit_sil" data-value="{{$taksit_gelen_vade->taksit_vade_id}}" href="#"><i class="dw dw-delete-3"></i> Sil</a>
                        </div>
                     </div>
                  </div>
               </div>
               @endforeach
               @foreach($senet_gelen_vadeler as $senet_gelen_vade)
               @php $kalem_sayisi++; @endphp
               <div class="row tahsilat_kalemleri_listesi senet_vadeleri_listesi tm-item-row senet-row" data-value="{{$senet_gelen_vade->senet_vade_id}}" style="margin:0 0 8px 0;">
                  <div class="tm-item-name"><i class="fa fa-file-text-o" style="color:#f59e0b;"></i> Senet Vadesi</div>
                  <div class="tm-item-meta"><i class="fa fa-calendar"></i> {{date('d.m.Y', strtotime($senet_gelen_vade->tarih))}}</div>
                  <div class="tm-item-qty"><span style="color:#64748b">1 adet</span></div>
                  <div class="tm-item-amount">
                     <input type="hidden" name="taksit_vade_id[]" value="{{$senet_gelen_vade->senet_vade_id}}">
                     <input type="hidden" class="form-control try-currency tahsilat_kalemleri" name="senet_tahsilat_tutari_girilen[]" value="{{number_format($senet_gelen_vade->tutar,2,',','.')}}">
                     <span style="font-weight:700; color:#1e293b;">{{number_format($senet_gelen_vade->tutar,2,',','.')}} ₺</span>
                  </div>
                  <div class="tm-item-actions">
                     <div class="dropdown">
                        <a class="btn btn-link p-0 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown" style="font-size:18px; color:#64748b;"><i class="dw dw-more"></i></a>
                        <div class="dropdown-menu dropdown-menu-right">
                           <a class="dropdown-item tahsilat_senet_sil" data-value="{{$senet_gelen_vade->senet_vade_id}}" href="#"><i class="dw dw-delete-3"></i> Sil</a>
                        </div>
                     </div>
                  </div>
               </div>
               @endforeach
            </div>

            @if($kalem_sayisi == 0)
            <div class="tm-empty">
               <i class="fa fa-inbox"></i>
               <p style="margin-top:8px;">Tahsil edilecek kalem yok. Yukarıdan hizmet/ürün/paket ekleyebilirsiniz.</p>
            </div>
            @endif
         </div>

         <!-- Tahsilat formu -->
         <div class="tm-card tek_tahsilat_formu" data-value="0">
            <div class="tm-card-title"><i class="fa fa-money"></i> Tahsilat Bilgileri</div>
            <div class="row">
               <div class="col-md-3 col-sm-6 col-6">
                  <div class="tm-field">
                     <label><i class="fa fa-calendar"></i> Tarih</label>
                     <input type="text" required name="tahsilat_tarihi" id='tahsilat_tarihi' value="{{date('Y-m-d')}}" autocomplete="off">
                  </div>
               </div>
               <div class="col-md-3 col-sm-6 col-6">
                  <div class="tm-field">
                     <label>Birim Tutar (₺)</label>
                     <input type="tel" class="try-currency tm-readonly" id='birim_tutar' value="">
                  </div>
               </div>
               <div class="col-md-3 col-sm-6 col-6">
                  <div class="tm-field">
                     <label>Müşteri İndirimi (%)</label>
                     <input type="hidden" id='musteri_indirimi' name="musteri_indirimi">
                     <input type="tel" id="musteri_indirim" value="{{$musteriIndirimYuzde}}" disabled class="tm-readonly">
                  </div>
               </div>
               <div class="col-md-3 col-sm-6 col-6">
                  <div class="tm-field">
                     <label>Harici İndirim (₺)</label>
                     <input type="tel" name="indirim_tutari" id='harici_indirim_tutari' class="try-currency">
                  </div>
               </div>
               <div class="col-md-4 col-sm-6 col-6">
                  <div class="tm-field">
                     <label><i class="fa fa-money"></i> Ödeme Yöntemi</label>
                     <select id='adisyon_tahsilat_odeme_yontemi' name="odeme_yontemi">
                        @foreach(\App\OdemeYontemleri::all() as $odeme_yontemi)
                        <option value="{{$odeme_yontemi->id}}">{{$odeme_yontemi->odeme_yontemi}}</option>
                        @endforeach
                     </select>
                  </div>
               </div>
               <div class="col-md-4 col-sm-6 col-6">
                  <div class="tm-field">
                     <label>Banka (opsiyonel)</label>
                     <select id='adisyon_tahsilat_banka' name="banka">
                        <option value=''>Seçiniz...</option>
                        @foreach(\App\SatisOrtakligiModel\Bankalar::all() as $banka)
                        <option value="{{$banka->id}}">{{$banka->banka}}</option>
                        @endforeach
                     </select>
                  </div>
               </div>
               <div class="col-md-4 col-sm-12 col-12">
                  <div class="tm-field">
                     <label style="color:#10b981;"><i class="fa fa-check-circle"></i> Ödenecek Tutar (₺)</label>
                     <input type="tel" class="try-currency tm-highlight" name="indirimli_toplam_tahsilat_tutari" id="indirimli_toplam_tahsilat_tutari" value="0">
                  </div>
               </div>
               <div class="col-md-4 col-sm-6 col-6">
                  <div class="tm-field">
                     <label>Kalan Alacak Tutarı (₺)</label>
                     <input type="tel" class="try-currency tm-readonly" name="odenecek_tutar" id='odenecek_tutar'>
                  </div>
               </div>
               <div class="col-md-8">
                  <button id='yeni_tahsilat_ekle' type="submit" class="tm-submit-btn" style="margin-top:12px;">
                     <i class="fa fa-money"></i> Tahsil Et
                  </button>
               </div>
            </div>
         </div>

      </div>

      <!-- Sağ kolon: Özet + Geçmiş -->
      <div class="col-md-4">
         <div class="tm-summary" id="odeme_kayit_bolumu">
            <div class="tm-summary-title">Toplam Alacak</div>
            <div class="tm-balance-amount" id="tahsil_edilecek_kalan_tutar">0,00 ₺</div>

            <div id="tahsilat_durumu">
               <div class="tm-summary-row">
                  <span>Ara Toplam</span>
                  <strong id="ara_toplam">0,00</strong>
               </div>
               <div class="tm-summary-row">
                  <span><i class="fa fa-percent" style="color:#7B2FB8;"></i> Müşteri İndirimi</span>
                  <strong id="uygulanan_indirim_tutari">0,00</strong>
               </div>
               <div class="tm-summary-row">
                  <span><i class="fa fa-tag" style="color:#f59e0b;"></i> Harici İndirim</span>
                  <strong id="uygulanan_harici_indirim_tutari">0,00</strong>
               </div>
               <div class="tm-summary-row paid">
                  <span><i class="fa fa-check"></i> Ödenen</span>
                  <strong id="tahsil_edilen_tutar">{{number_format($tahsilatlar->sum('tutar'),2,',','.')}}</strong>
               </div>
               <div class="tm-summary-row total">
                  <span>Kalan Alacak</span>
                  <strong class="tahsil_edilecek_kalan_tutar">0,00</strong>
               </div>
            </div>

            <div class="tm-history">
               <div class="tm-history-title"><i class="fa fa-history"></i> Geçmiş Ödemeler</div>
               <div id="tahsilat_listesi">
                  @if($tahsilatlar->count() == 0)
                     <div class="tm-empty" style="padding:20px 12px;">
                        <small>Henüz ödeme yapılmamış</small>
                     </div>
                  @else
                     @foreach($tahsilatlar as $key=>$tahsilat)
                     <div class="tm-history-row">
                        <span class="h-date">{{date('d.m.Y',strtotime($tahsilat->odeme_tarihi))}}</span>
                        <span class="h-amount">{{number_format($tahsilat->tutar,2,',','.')}} ₺</span>
                        <span class="h-method">{{$tahsilat->odeme_yontemi->odeme_yontemi}}</span>
                        <button type="button" class="h-del btn btn-danger" name="tahsilat_adisyondan_sil" data-value="{{$tahsilat->id}}" title="Sil">
                           <i class="icon-copy fa fa-remove"></i>
                        </button>
                     </div>
                     @endforeach
                  @endif
               </div>
            </div>
         </div>
      </div>
   </div>
</form>

</div>
@endsection
