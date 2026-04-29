@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
{!!csrf_field()!!}

@php
   $musteri_adisyon_count = \App\Adisyonlar::where('user_id',$musteri->id)->where('salon_id',$isletme->id)->count();
   $sadikSart = $musteri_adisyon_count>3 && date('Y-m-d H:i:s', strtotime('+90 days',strtotime(\App\Adisyonlar::where('user_id',$musteri->id)->where('salon_id',$isletme->id)->orderBy('id','desc')->value('created_at')))) < date('Y-m-d H:i:s', strtotime('+90 days', strtotime(date('Y-m-d H:i:s'))));
   if($sadikSart){ $musteriTipi = 'sadik'; $musteriEtiket = 'Sadık'; $musteriIndirimYuzde = $isletme->sadik_musteri_indirim_yuzde; }
   elseif($musteri_adisyon_count==0){ $musteriTipi='pasif'; $musteriEtiket='Pasif'; $musteriIndirimYuzde=$isletme->pasif_musteri_indirim_yuzde; }
   else{ $musteriTipi='aktif'; $musteriEtiket='Aktif'; $musteriIndirimYuzde=$isletme->aktif_musteri_indirim_yuzde; }
   $adisyonTarih = ($adisyon && $adisyon->tarih) ? date('Y-m-d', strtotime($adisyon->tarih)) : '';
   $adisyonTarihGoster = $adisyonTarih ? date('d.m.Y', strtotime($adisyonTarih)) : '—';
@endphp

<style>
   .tm{ font-family:'Inter','Segoe UI',sans-serif; }
   .tm *{ box-sizing:border-box; }

   /* === Compact top bar === */
   .tm-bar{
      display:flex; align-items:center; gap:14px; flex-wrap:wrap;
      background:#fff; border:1px solid #e5e7eb; border-radius:10px;
      padding:8px 14px; margin-bottom:12px; font-size:13px;
   }
   .tm-bar .tm-customer{ font-weight:700; color:#0f172a; font-size:15px; display:flex; align-items:center; gap:8px; }
   .tm-bar .tm-customer i{ color:#7B2FB8; }
   .tm-bar .tm-sep{ width:1px; height:18px; background:#e5e7eb; }
   .tm-bar .tm-meta{ color:#64748b; font-size:12.5px; }
   .tm-bar .tm-meta strong{ color:#0f172a; }
   .tm-pill{ display:inline-flex; align-items:center; gap:5px; padding:2px 9px; border-radius:14px; font-size:11px; font-weight:700; letter-spacing:.2px; }
   .tm-pill.aktif{ background:#dbeafe; color:#1e40af; }
   .tm-pill.sadik{ background:#fef3c7; color:#92400e; }
   .tm-pill.pasif{ background:#f1f5f9; color:#475569; }
   .tm-pill.beta{ background:linear-gradient(135deg,#5C008E,#9D5DC8); color:#fff; }

   .tm-bar-actions{ margin-left:auto; display:flex; align-items:center; gap:8px; }
   .tm-mini-btn{
      background:#f1f5f9; border:1px solid #e2e8f0; color:#475569;
      padding:5px 10px; border-radius:7px; font-size:12px; font-weight:600;
      cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:5px;
   }
   .tm-mini-btn:hover{ background:#e2e8f0; color:#1e293b; text-decoration:none; }
   .tm-tarih-edit input{ height:26px; border:1px solid #cbd5e1; border-radius:6px; padding:0 6px; font-size:12px; }

   /* === HERO checkout card === */
   .tm-hero{
      display:grid; grid-template-columns: 1.1fr 1.4fr; gap:0;
      background:#fff; border-radius:14px; overflow:hidden;
      box-shadow:0 8px 24px -10px rgba(15,23,42,.18); border:1px solid #e5e7eb;
      margin-bottom:14px;
   }
   .tm-hero-balance{
      background:linear-gradient(135deg,#fff5f5 0%,#fff 100%);
      padding:22px 26px; display:flex; flex-direction:column; justify-content:center;
      border-right:1px solid #f1f5f9;
   }
   .tm-hero-balance.zero{ background:linear-gradient(135deg,#ecfdf5 0%,#fff 100%); }
   .tm-hero-label{
      font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase;
      letter-spacing:1px; margin-bottom:4px;
   }
   .tm-hero-amount{
      font-size:42px; font-weight:800; color:#dc2626; line-height:1;
      letter-spacing:-.5px;
   }
   .tm-hero-amount.zero{ color:#10b981; }
   .tm-hero-amount-suffix{ font-size:22px; font-weight:600; opacity:.7; margin-left:4px; }
   .tm-hero-summary{
      display:flex; gap:14px; margin-top:14px; flex-wrap:wrap;
      padding-top:14px; border-top:1px dashed #e5e7eb;
   }
   .tm-hero-summary div{ font-size:11.5px; color:#64748b; }
   .tm-hero-summary div b{ color:#0f172a; display:block; font-size:13px; }

   .tm-hero-pay{
      padding:18px 22px; background:#fafbff;
      display:flex; flex-direction:column; gap:10px;
   }
   .tm-pay-row{ display:grid; grid-template-columns: 1fr 1fr; gap:10px; }
   .tm-pay-row.three{ grid-template-columns: 1fr 1fr 1fr; }
   .tm-input-group{ display:flex; flex-direction:column; }
   .tm-input-group label{
      font-size:10.5px; font-weight:700; color:#64748b;
      text-transform:uppercase; letter-spacing:.4px; margin-bottom:3px;
   }
   .tm-input-group input, .tm-input-group select{
      height:38px; padding:0 11px; border:1px solid #d1d5db; border-radius:8px;
      font-size:14px; background:#fff; width:100%;
   }
   .tm-input-group input:focus, .tm-input-group select:focus{
      outline:none; border-color:#7B2FB8; box-shadow:0 0 0 3px rgba(123,47,184,.1);
   }
   .tm-input-group input.tm-amount{
      font-size:18px; font-weight:700; color:#065f46;
      background:#ecfdf5; border-color:#10b981; height:46px;
   }
   .tm-input-group input.tm-readonly{ background:#f3f4f6; color:#6b7280; }
   .tm-pay-actions{ display:flex; gap:8px; align-items:stretch; }
   .tm-pay-actions .tm-pay-extra{
      background:#fff; border:1px dashed #cbd5e1; color:#475569;
      padding:0 12px; border-radius:9px; font-size:12px; font-weight:600;
      display:flex; align-items:center; gap:5px; cursor:pointer; flex:0 0 auto;
   }
   .tm-pay-actions .tm-pay-extra:hover{ background:#f8fafc; border-color:#7B2FB8; color:#7B2FB8; }
   .tm-pay-btn{
      flex:1; height:50px; border:none; border-radius:10px;
      background:linear-gradient(135deg,#10b981 0%,#059669 100%); color:#fff;
      font-size:16px; font-weight:700; letter-spacing:.3px; cursor:pointer;
      box-shadow:0 6px 14px -4px rgba(16,185,129,.45);
      transition:transform .12s ease, box-shadow .12s ease;
      display:flex; align-items:center; justify-content:center; gap:8px;
   }
   .tm-pay-btn:hover{ transform:translateY(-1px); box-shadow:0 10px 18px -4px rgba(16,185,129,.55); color:#fff; }
   .tm-pay-btn:active{ transform:translateY(0); }

   /* Hidden secondary fields togglable */
   .tm-extra-row{
      display:none; grid-template-columns:1fr 1fr 1fr; gap:10px;
      padding:10px 12px; background:#fff; border:1px dashed #cbd5e1;
      border-radius:9px; margin-top:4px;
   }
   .tm-extra-row.open{ display:grid; }

   /* === Items + actions === */
   .tm-section{
      background:#fff; border:1px solid #e5e7eb; border-radius:12px;
      padding:14px 16px; margin-bottom:12px;
      box-shadow:0 2px 8px -4px rgba(15,23,42,.06);
   }
   .tm-section-head{
      display:flex; justify-content:space-between; align-items:center;
      margin-bottom:10px; flex-wrap:wrap; gap:8px;
   }
   .tm-section-title{ font-size:13px; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:.4px; display:flex; align-items:center; gap:6px; }
   .tm-section-title i{ color:#7B2FB8; }
   .tm-section-title .count{ background:#7B2FB8; color:#fff; font-size:10px; padding:1px 7px; border-radius:10px; }

   .tm-add-buttons{ display:flex; gap:6px; flex-wrap:wrap; }
   .tm-add-btn{
      padding:5px 11px; font-size:12px; font-weight:600; border-radius:7px;
      border:1px solid; cursor:pointer; display:inline-flex; align-items:center; gap:5px;
      transition:all .12s ease;
   }
   .tm-add-btn.hizmet{ background:#eff6ff; border-color:#3b82f6; color:#1d4ed8; }
   .tm-add-btn.hizmet:hover{ background:#3b82f6; color:#fff; }
   .tm-add-btn.urun{ background:#fef2f2; border-color:#ef4444; color:#b91c1c; }
   .tm-add-btn.urun:hover{ background:#ef4444; color:#fff; }
   .tm-add-btn.paket{ background:#faf5ff; border-color:#8b5cf6; color:#6d28d9; }
   .tm-add-btn.paket:hover{ background:#8b5cf6; color:#fff; }
   .tm-add-btn.taksit{ background:#fffbeb; border-color:#f59e0b; color:#b45309; }
   .tm-add-btn.taksit:hover{ background:#f59e0b; color:#fff; }
   .tm-add-btn.alacak{ background:#f0fdf4; border-color:#10b981; color:#047857; }
   .tm-add-btn.alacak:hover{ background:#10b981; color:#fff; }

   .tm-item{
      display:grid;
      grid-template-columns: 28px 1fr 130px 110px 150px 30px;
      gap:10px; align-items:center;
      padding:8px 10px; background:#f8fafc; border-radius:8px;
      border-left:3px solid #7B2FB8; margin-bottom:5px;
      font-size:13px;
   }
   .tm-item:hover{ background:#f1f5f9; }
   .tm-item.taksit-row{ border-left-color:#0ea5e9; background:#eff6ff; }
   .tm-item.senet-row{ border-left-color:#f59e0b; background:#fffbeb; }
   .tm-item-icon{
      width:26px; height:26px; border-radius:6px; display:flex; align-items:center; justify-content:center;
      background:#fff; color:#7B2FB8; font-size:12px;
   }
   .tm-item.urun-row .tm-item-icon{ color:#dc2626; }
   .tm-item.paket-row .tm-item-icon{ color:#0ea5e9; }
   .tm-item-name{ font-weight:600; color:#0f172a; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
   .tm-item-meta{ color:#64748b; font-size:12px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
   .tm-item-qty input{ height:28px; font-size:12px; padding:0 6px; border:1px solid #cbd5e1; border-radius:6px; width:100%; }
   .tm-item-amount{ display:flex; align-items:center; gap:5px; justify-content:flex-end; }
   .tm-item-amount input{ height:30px; font-size:13px; font-weight:700; padding:0 8px; border:1px solid #cbd5e1; border-radius:6px; text-align:right; width:108px; color:#0f172a; }
   .tm-item-amount .tl{ color:#94a3b8; font-size:11px; }
   .tm-empty{
      padding:18px 12px; text-align:center; color:#94a3b8;
      background:#f8fafc; border:1px dashed #e2e8f0; border-radius:8px;
   }

   /* === Past payments rail (right column) === */
   .tm-history{
      background:#fff; border:1px solid #e5e7eb; border-radius:12px;
      padding:14px 16px; margin-bottom:12px;
      box-shadow:0 2px 8px -4px rgba(15,23,42,.06);
   }
   .tm-history-row{
      display:flex; align-items:center; gap:8px; padding:7px 9px;
      background:#f8fafc; border-radius:7px; margin-bottom:4px; font-size:12px;
   }
   .tm-history-row .h-date{ color:#64748b; min-width:68px; }
   .tm-history-row .h-amount{ font-weight:700; color:#0f172a; flex:1; }
   .tm-history-row .h-method{ color:#7B2FB8; font-size:10.5px; background:#f3e8ff; padding:1px 7px; border-radius:6px; font-weight:600; }
   .tm-history-row .h-del{
      background:transparent; border:none; color:#dc2626; padding:1px 4px; cursor:pointer; line-height:1;
   }

   @media (max-width: 1100px){
      .tm-hero{ grid-template-columns:1fr; }
      .tm-hero-balance{ border-right:none; border-bottom:1px solid #f1f5f9; padding:18px 22px 14px 22px; }
      .tm-hero-amount{ font-size:36px; }
      .tm-item{ grid-template-columns: 28px 1fr 90px 90px 130px 30px; }
   }
   @media (max-width: 700px){
      .tm-pay-row, .tm-pay-row.three{ grid-template-columns:1fr; }
      .tm-extra-row{ grid-template-columns:1fr 1fr; }
      .tm-item{ grid-template-columns:1fr; gap:6px; padding:10px; }
      .tm-item-amount input{ width:100%; }
   }
</style>

<div class="tm">

   <!-- ===== Compact Top Bar ===== -->
   <div class="tm-bar">
      <div class="tm-customer"><i class="fa fa-user"></i> {{ $musteri->name }}</div>
      <div class="tm-sep"></div>
      <div class="tm-meta">Satış <strong>#{{ $adisyon_id }}</strong></div>
      <div class="tm-sep"></div>
      <span class="tm-pill {{ $musteriTipi }}">{{ $musteriEtiket }} · %{{ $musteriIndirimYuzde }}</span>
      <div class="tm-sep"></div>
      <div class="tm-tarih-edit" style="display:flex; align-items:center; gap:6px; font-size:12px; color:#64748b;">
         <i class="fa fa-calendar"></i> Satış:
         <strong id="tm-satis-tarihi-goster" style="color:#0f172a;">{{$adisyonTarihGoster}}</strong>
         <button type="button" id="tm-satis-tarihi-duzenle" class="tm-mini-btn" title="Tarihi düzenle"><i class="fa fa-pencil"></i></button>
         <input type="date" id="tm-satis-tarihi-input" value="{{$adisyonTarih}}" style="display:none;">
         <button type="button" id="tm-satis-tarihi-kaydet" class="tm-mini-btn" style="display:none; background:#10b981; color:#fff; border-color:#10b981;"><i class="fa fa-check"></i></button>
         <button type="button" id="tm-satis-tarihi-iptal" class="tm-mini-btn" style="display:none;">×</button>
      </div>
      <div class="tm-bar-actions">
         <span class="tm-pill beta"><i class="fa fa-flask"></i> BETA</span>
         <a href="/isletmeyonetim/tahsilat/{{$musteri->id}}/{{$adisyon_id}}{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="tm-mini-btn" title="Eski tasarım">
            <i class="fa fa-arrow-left"></i> Eski
         </a>
      </div>
   </div>

<form id="adisyon_tahsilat" method="POST">
   <input type="hidden" name="tahsilat_ekrani" id="tahsilat_ekrani" value="1">
   <select style="display:none" name="tahsilat_musteri_id" id='tahsilat_musteri_id'>
      <option selected value="{{$musteri->id}}">777</option>
   </select>
   <input type="hidden" name="tahsilat_tutari" id='toplam_tahsilat_tutari'>
   <input type="hidden" name="sube" value="{{$isletme->id}}">
   <input type="hidden" name="adisyon_id" value="{{$adisyon_id}}">

   <div class="row">
      <div class="col-lg-9">

         <!-- ===== HERO CHECKOUT ===== -->
         <div class="tm-hero tek_tahsilat_formu" data-value="0">
            <!-- Sol: Alacak -->
            <div class="tm-hero-balance">
               <div class="tm-hero-label">Tahsil Edilecek Alacak</div>
               <div>
                  <span class="tm-hero-amount" id="tahsil_edilecek_kalan_tutar">0,00</span>
                  <span class="tm-hero-amount-suffix">₺</span>
               </div>
               <div class="tm-hero-summary">
                  <div>Ara Toplam<b id="ara_toplam">0,00 ₺</b></div>
                  <div>Müşteri İnd.<b id="uygulanan_indirim_tutari">0,00 ₺</b></div>
                  <div>Harici İnd.<b id="uygulanan_harici_indirim_tutari">0,00 ₺</b></div>
                  <div>Ödenen<b id="tahsil_edilen_tutar" style="color:#059669;">{{number_format($tahsilatlar->sum('tutar'),2,',','.')}} ₺</b></div>
               </div>
            </div>

            <!-- Sağ: Hızlı tahsilat formu -->
            <div class="tm-hero-pay">
               <div class="tm-pay-row">
                  <div class="tm-input-group">
                     <label>Ödenecek Tutar (₺)</label>
                     <input type="tel" class="try-currency tm-amount" name="indirimli_toplam_tahsilat_tutari" id="indirimli_toplam_tahsilat_tutari" value="0">
                  </div>
                  <div class="tm-input-group">
                     <label>Ödeme Yöntemi</label>
                     <select id='adisyon_tahsilat_odeme_yontemi' name="odeme_yontemi">
                        @foreach(\App\OdemeYontemleri::all() as $odeme_yontemi)
                        <option value="{{$odeme_yontemi->id}}">{{$odeme_yontemi->odeme_yontemi}}</option>
                        @endforeach
                     </select>
                  </div>
               </div>

               <div class="tm-pay-actions">
                  <button type="button" class="tm-pay-extra" id="tm-extra-toggle" title="Tarih, banka, indirim alanlarını aç">
                     <i class="fa fa-cog"></i> Detay
                  </button>
                  <button id='yeni_tahsilat_ekle' type="submit" class="tm-pay-btn">
                     <i class="fa fa-check-circle"></i> TAHSİL ET
                  </button>
               </div>

               <!-- Detay (gizli, açılınca görünür) -->
               <div class="tm-extra-row" id="tm-extra-row">
                  <div class="tm-input-group">
                     <label>Tarih</label>
                     <input type="text" required name="tahsilat_tarihi" id='tahsilat_tarihi' value="{{date('Y-m-d')}}" autocomplete="off">
                  </div>
                  <div class="tm-input-group">
                     <label>Banka</label>
                     <select id='adisyon_tahsilat_banka' name="banka">
                        <option value=''>—</option>
                        @foreach(\App\SatisOrtakligiModel\Bankalar::all() as $banka)
                        <option value="{{$banka->id}}">{{$banka->banka}}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="tm-input-group">
                     <label>Harici İndirim (₺)</label>
                     <input type="tel" name="indirim_tutari" id='harici_indirim_tutari' class="try-currency">
                  </div>
                  <div class="tm-input-group">
                     <label>Birim Tutar</label>
                     <input type="tel" class="try-currency tm-readonly" id='birim_tutar' value="" readonly>
                  </div>
                  <div class="tm-input-group">
                     <label>Müşteri İnd. (%)</label>
                     <input type="hidden" id='musteri_indirimi' name="musteri_indirimi">
                     <input type="tel" id="musteri_indirim" value="{{$musteriIndirimYuzde}}" disabled class="tm-readonly">
                  </div>
                  <div class="tm-input-group">
                     <label>Kalan Alacak</label>
                     <input type="tel" class="try-currency tm-readonly" name="odenecek_tutar" id='odenecek_tutar' readonly>
                  </div>
               </div>
            </div>
         </div>

         <!-- ===== Items section ===== -->
         <div class="tm-section">
            <div class="tm-section-head">
               <div class="tm-section-title">
                  <i class="fa fa-list"></i> Tahsil Edilecek Kalemler
                  @php $ks=0;@endphp
               </div>
               <div class="tm-add-buttons">
                  <button type="button" class="tm-add-btn hizmet adisyon_ekle_buttonlar" data-toggle="modal" data-target="#adisyon_yeni_hizmet_modal" id="adisyon_hizmet_ekle_button">
                     <i class="fa fa-plus"></i> Hizmet
                  </button>
                  <button type="button" class="tm-add-btn urun adisyon_ekle_buttonlar" data-toggle="modal" id="adisyon_urun_ekle_button" data-target="#urun_satisi_modal" data-value='' onclick="modalbaslikata('Yeni Ürün Satışı Ekle','')">
                     <i class="fa fa-plus"></i> Ürün
                  </button>
                  <button type="button" class="tm-add-btn paket adisyon_ekle_buttonlar" data-toggle="modal" id="adisyon_paket_ekle_button" data-target="#paket_satisi_modal" data-value=''>
                     <i class="fa fa-plus"></i> Paket
                  </button>
                  <span style="width:1px; background:#e5e7eb; margin:0 2px;"></span>
                  <button type="button" class="tm-add-btn taksit" id='yeni_taksitli_tahsilat_olusur' data-value=''>
                     <i class="fa fa-credit-card"></i> Taksit Yap
                  </button>
                  <button type="button" class="tm-add-btn alacak" id='senetle_veya_taksitle_tahsil_et'>
                     <i class="fa fa-list-alt"></i> Alacaklar
                  </button>
               </div>
            </div>

            <div id='tum_tahsilatlar'>
               @foreach(\App\Adisyonlar::whereIn('id',$acik_adisyonlar->pluck('id'))->get() as $adisyon)

                  @foreach($adisyon->hizmetler as $key=>$hizmet)
                     @if(($hizmet->fiyat - \App\TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar') - $hizmet->indirim_tutari >= 0 || $hizmet->hediye) && $hizmet->senet_id === null && $hizmet->taksitli_tahsilat_id === null)
                     @php $ks++; @endphp
                     <div class="tahsilat_kalemleri_listesi tm-item hizmet-row" data-value="0">
                        <div class="tm-item-icon"><i class="fa fa-cut"></i></div>
                        <div class="tm-item-name">{{ ($hizmet->hizmet_id != null ? $hizmet->hizmet->hizmet_adi : '') }}</div>
                        <div class="tm-item-meta">
                           @if($hizmet->personel_id !== null){{$hizmet->personel->personel_adi}}
                           @elseif($hizmet->cihaz_id !== null){{$hizmet->cihaz->cihaz_adi}}
                           @endif
                        </div>
                        <div class="tm-item-qty">
                           <input type="tel" value="{{$hizmet->seans_sayisi}}" data-value="{{$hizmet->id}}" class="form-control" name="hizmet_seans_girilen[]" title="seans">
                        </div>
                        <div class="tm-item-amount">
                           <input type="hidden" name="adisyon_hizmet_id[]" value="{{$hizmet->id}}">
                           <input type="hidden" name="indirim[]" data-value="{{$hizmet->id}}" value="{{$hizmet->indirim_tutari}}">
                           <input type="hidden" name="adisyon_hizmet_senet_id[]" value="{{$hizmet->senet_id}}">
                           <input type="hidden" name="adisyon_hizmet_taksitli_tahsilat_id[]" value="{{$hizmet->taksitli_tahsilat_id}}">
                           <input type="tel" class="form-control try-currency tahsilat_kalemleri" name="himzet_tahsilat_tutari_girilen[]" data-value="{{$hizmet->id}}" value="{{number_format($hizmet->fiyat - \App\TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar') - $hizmet->indirim_tutari,2,',','.')}}">
                           <span class="tl">₺</span>
                           @if($hizmet->hediye)<i class="fa fa-gift" style="color:#f59e0b;" title="Hediye"></i>@endif
                        </div>
                        <div class="dropdown">
                           <a class="btn btn-link p-0 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown" style="font-size:16px; color:#94a3b8;"><i class="dw dw-more"></i></a>
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
                     @endif
                  @endforeach

                  @foreach($adisyon->urunler as $key=>$urun)
                     @if(($urun->fiyat - \App\TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar') - $urun->indirim_tutari >= 0 || $urun->hediye) && $urun->senet_id === null && $urun->taksitli_tahsilat_id===null)
                     @php $ks++; @endphp
                     <div class="tahsilat_kalemleri_listesi tm-item urun-row" data-value="0">
                        <div class="tm-item-icon"><i class="fa fa-shopping-bag"></i></div>
                        <div class="tm-item-name">{{$urun->urun->urun_adi}}</div>
                        <div class="tm-item-meta">@if($urun->personel_id){{$urun->personel->personel_adi}}@endif</div>
                        <div class="tm-item-qty">
                           @if(($urun->senet_id == null && $urun->taksitli_tahsilat_id == null && \App\TahsilatUrunler::where('adisyon_urun_id',$urun->id)->count()==0) || $urun->fiyat == 0)
                              <input type="tel" value="{{$urun->adet}}" data-value="{{$urun->id}}" class="form-control" name="urun_adet_girilen[]" title="adet">
                           @else
                              <span style="color:#64748b; font-size:12px;">{{$urun->adet}} adet</span>
                           @endif
                        </div>
                        <div class="tm-item-amount">
                           <input type="hidden" name="adisyon_urun_id[]" value="{{$urun->id}}">
                           <input type="hidden" name="indirim[]" data-value="{{$urun->id}}" value="{{$urun->indirim_tutari}}">
                           <input type="hidden" name="adisyon_urun_senet_id[]" value="{{$urun->senet_id}}">
                           <input type="hidden" name="adisyon_urun_taksitli_tahsilat_id[]" value="{{$urun->taksitli_tahsilat_id}}">
                           <input type="tel" class="form-control try-currency tahsilat_kalemleri" name="urun_tahsilat_tutari_girilen[]" data-value="{{$urun->id}}" value="{{number_format($urun->fiyat - \App\TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar') - $urun->indirim_tutari,2,',','.')}}">
                           <span class="tl">₺</span>
                           @if($urun->hediye)<i class="fa fa-gift" style="color:#f59e0b;" title="Hediye"></i>@endif
                        </div>
                        <div class="dropdown">
                           <a class="btn btn-link p-0 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown" style="font-size:16px; color:#94a3b8;"><i class="dw dw-more"></i></a>
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
                     @endif
                  @endforeach

                  @foreach($adisyon->paketler as $key=>$paket)
                     @if(($paket->fiyat - \App\TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar') - $paket->indirim_tutari >= 0 || $paket->hediye) && $paket->senet_id === null && $paket->taksitli_tahsilat_id === null)
                     @php $ks++; @endphp
                     <div class="tahsilat_kalemleri_listesi tm-item paket-row" data-value="0">
                        <div class="tm-item-icon"><i class="fa fa-cubes"></i></div>
                        <div class="tm-item-name">{{$paket->paket->paket_adi}}</div>
                        <div class="tm-item-meta">@if($paket->personel_id){{$paket->personel->personel_adi}}@endif</div>
                        <div class="tm-item-qty">
                           <input type="tel" value="{{$paket->seans_sayisi}}" data-value="{{$paket->id}}" class="form-control" name="paket_seans_girilen[]" title="seans">
                        </div>
                        <div class="tm-item-amount">
                           <input type="hidden" name="adisyon_paket_id[]" value="{{$paket->id}}">
                           <input type="hidden" name="adisyon_paket_senet_id[]" value="{{$paket->senet_id}}">
                           <input type="hidden" name="adisyon_paket_taksitli_tahsilat_id[]" value="{{$paket->taksitli_tahsilat_id}}">
                           <input type="hidden" name="indirim[]" data-value="{{$paket->id}}" value="{{$paket->indirim_tutari}}">
                           <input type="tel" class="form-control try-currency tahsilat_kalemleri" name="paket_tahsilat_tutari_girilen[]" data-value="{{$paket->id}}" value="{{number_format($paket->fiyat - \App\TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar') - $paket->indirim_tutari,2,',','.')}}">
                           <span class="tl">₺</span>
                           @if($paket->hediye)<i class="fa fa-gift" style="color:#f59e0b;" title="Hediye"></i>@endif
                        </div>
                        <div class="dropdown">
                           <a class="btn btn-link p-0 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown" style="font-size:16px; color:#94a3b8;"><i class="dw dw-more"></i></a>
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
                     @endif
                  @endforeach
               @endforeach
            </div>

            <div id="taksitli_ve_senetli_tahsilatlar">
               @foreach($taksit_gelen_vadeler as $taksit_gelen_vade)
               @php $ks++; @endphp
               <div class="tahsilat_kalemleri_listesi taksit_vadeleri_listesi tm-item taksit-row" data-value="{{$taksit_gelen_vade->taksit_vade_id}}">
                  <div class="tm-item-icon" style="color:#0ea5e9;"><i class="fa fa-credit-card"></i></div>
                  <div class="tm-item-name">Taksit Vadesi</div>
                  <div class="tm-item-meta"><i class="fa fa-calendar"></i> {{date('d.m.Y', strtotime($taksit_gelen_vade->tarih))}}</div>
                  <div class="tm-item-qty"><span style="color:#64748b; font-size:12px;">1 adet</span></div>
                  <div class="tm-item-amount">
                     <input type="hidden" name="taksit_vade_id[]" value="{{$taksit_gelen_vade->taksit_vade_id}}">
                     <input type="hidden" class="form-control try-currency tahsilat_kalemleri" name="taksit_tahsilat_tutari_girilen[]" value="{{number_format($taksit_gelen_vade->tutar,2,',','.')}}">
                     <span style="font-weight:700; color:#0f172a;">{{number_format($taksit_gelen_vade->tutar,2,',','.')}}</span>
                     <span class="tl">₺</span>
                  </div>
                  <div class="dropdown">
                     <a class="btn btn-link p-0 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown" style="font-size:16px; color:#94a3b8;"><i class="dw dw-more"></i></a>
                     <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item tahsilat_taksit_sil" data-value="{{$taksit_gelen_vade->taksit_vade_id}}" href="#"><i class="dw dw-delete-3"></i> Sil</a>
                     </div>
                  </div>
               </div>
               @endforeach
               @foreach($senet_gelen_vadeler as $senet_gelen_vade)
               @php $ks++; @endphp
               <div class="tahsilat_kalemleri_listesi senet_vadeleri_listesi tm-item senet-row" data-value="{{$senet_gelen_vade->senet_vade_id}}">
                  <div class="tm-item-icon" style="color:#f59e0b;"><i class="fa fa-file-text-o"></i></div>
                  <div class="tm-item-name">Senet Vadesi</div>
                  <div class="tm-item-meta"><i class="fa fa-calendar"></i> {{date('d.m.Y', strtotime($senet_gelen_vade->tarih))}}</div>
                  <div class="tm-item-qty"><span style="color:#64748b; font-size:12px;">1 adet</span></div>
                  <div class="tm-item-amount">
                     <input type="hidden" name="taksit_vade_id[]" value="{{$senet_gelen_vade->senet_vade_id}}">
                     <input type="hidden" class="form-control try-currency tahsilat_kalemleri" name="senet_tahsilat_tutari_girilen[]" value="{{number_format($senet_gelen_vade->tutar,2,',','.')}}">
                     <span style="font-weight:700; color:#0f172a;">{{number_format($senet_gelen_vade->tutar,2,',','.')}}</span>
                     <span class="tl">₺</span>
                  </div>
                  <div class="dropdown">
                     <a class="btn btn-link p-0 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown" style="font-size:16px; color:#94a3b8;"><i class="dw dw-more"></i></a>
                     <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item tahsilat_senet_sil" data-value="{{$senet_gelen_vade->senet_vade_id}}" href="#"><i class="dw dw-delete-3"></i> Sil</a>
                     </div>
                  </div>
               </div>
               @endforeach
            </div>

            @if($ks == 0)
            <div class="tm-empty">
               <i class="fa fa-inbox" style="font-size:24px; opacity:.4;"></i>
               <div style="margin-top:6px; font-size:13px;">Tahsil edilecek kalem yok. Yukarıdan ekleyebilirsin.</div>
            </div>
            @endif
         </div>

         <!-- Hidden duplicate balance row for JS class hooks -->
         <span class="tahsil_edilecek_kalan_tutar" style="display:none;"></span>
      </div>

      <!-- ===== Right rail: Past payments ===== -->
      <div class="col-lg-3">
         <div class="tm-history">
            <div class="tm-section-title" style="margin-bottom:10px;"><i class="fa fa-history"></i> Geçmiş Ödemeler</div>
            <div id="tahsilat_listesi">
               @if($tahsilatlar->count() == 0)
                  <div class="tm-empty" style="padding:14px 8px;">
                     <small>Henüz ödeme yok</small>
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

   <div id="odeme_kayit_bolumu" style="display:none;">
      <table style="display:none;"><thead id="tahsilat_durumu"></thead></table>
   </div>
</form>

</div>

<script>
   document.addEventListener('DOMContentLoaded', function(){
      // ----- Detay (extra row) toggle -----
      var extraToggle = document.getElementById('tm-extra-toggle');
      var extraRow = document.getElementById('tm-extra-row');
      if(extraToggle && extraRow){
         extraToggle.addEventListener('click', function(){
            extraRow.classList.toggle('open');
            extraToggle.classList.toggle('active');
         });
      }

      // ----- Kalan Alacak rengi (yeşil/kırmızı) -----
      var observer = new MutationObserver(function(){
         var el = document.getElementById('tahsil_edilecek_kalan_tutar');
         if(!el) return;
         var v = parseFloat((el.textContent||'0').replace(/\./g,'').replace(',','.'));
         var balance = el.parentElement && el.parentElement.parentElement ? el.parentElement.parentElement : null;
         var amount = el;
         if(amount){
            if(v <= 0.001){ amount.classList.add('zero'); if(balance) balance.classList.add('zero'); }
            else { amount.classList.remove('zero'); if(balance) balance.classList.remove('zero'); }
         }
      });
      var target = document.getElementById('tahsil_edilecek_kalan_tutar');
      if(target) observer.observe(target,{childList:true, characterData:true, subtree:true});

      // ----- Satış tarihi inline edit -----
      var goster = document.getElementById('tm-satis-tarihi-goster');
      var btnDuzenle = document.getElementById('tm-satis-tarihi-duzenle');
      var input = document.getElementById('tm-satis-tarihi-input');
      var btnKaydet = document.getElementById('tm-satis-tarihi-kaydet');
      var btnIptal = document.getElementById('tm-satis-tarihi-iptal');
      var orijinalDeger = input ? input.value : '';

      if(btnDuzenle){
         btnDuzenle.addEventListener('click', function(){
            goster.style.display='none';
            btnDuzenle.style.display='none';
            input.style.display='inline-block';
            btnKaydet.style.display='inline-flex';
            btnIptal.style.display='inline-flex';
            input.focus();
         });
         btnIptal.addEventListener('click', function(){
            input.value = orijinalDeger;
            goster.style.display='inline';
            btnDuzenle.style.display='inline-flex';
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
               btnKaydet.disabled=false; btnKaydet.innerHTML='<i class="fa fa-check"></i>';
               if(j.ok){
                  goster.textContent = j.tarih_format;
                  orijinalDeger = j.tarih;
                  input.value = j.tarih;
                  goster.style.display='inline';
                  btnDuzenle.style.display='inline-flex';
                  input.style.display='none';
                  btnKaydet.style.display='none';
                  btnIptal.style.display='none';
               } else { alert(j.mesaj || 'Güncelleme başarısız'); }
            }).catch(function(){
               btnKaydet.disabled=false; btnKaydet.innerHTML='<i class="fa fa-check"></i>';
               alert('Bir hata oluştu');
            });
         });
      }
   });
</script>

@endsection
