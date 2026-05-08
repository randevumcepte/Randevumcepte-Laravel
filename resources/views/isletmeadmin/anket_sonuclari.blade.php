@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

<style>
   .akt-card { background:#fff; border-radius:10px; box-shadow:0 2px 8px rgba(92,0,142,.06); padding:16px 20px; }
   .istat-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px; margin-bottom:18px; }
   .istat-kart { background:#fff; border-radius:10px; padding:14px 18px; border:1px solid #ece6f3; box-shadow:0 2px 8px rgba(92,0,142,.04); }
   .istat-kart .et { font-size:11px; color:#8a8295; font-weight:600; text-transform:uppercase; letter-spacing:.4px; }
   .istat-kart .deg { font-size:24px; font-weight:800; color:#3a1a52; margin-top:4px; line-height:1.1; }
   .istat-kart .alt-deg { font-size:11px; color:#8a8295; margin-top:2px; }
   .istat-kart.nps .deg { color:#5C008E; }
   .istat-kart.csat .deg { color:#f59e0b; }

   .nps-bar-wrap { display:flex; height:30px; border-radius:6px; overflow:hidden; margin:12px 0 8px; }
   .nps-bar-detractor { background:#ef4444; }
   .nps-bar-passive   { background:#f59e0b; }
   .nps-bar-promoter  { background:#10b981; }
   .nps-bar-wrap > div { color:#fff; font-size:12px; font-weight:700; text-align:center; line-height:30px; }
   .nps-legend { display:flex; gap:16px; font-size:11.5px; color:#5b6770; flex-wrap:wrap; }
   .nps-legend > div { display:flex; align-items:center; gap:5px; }
   .nps-legend .renk { width:11px; height:11px; border-radius:3px; display:inline-block; }

   .gtablo th { background:#faf5ff; color:#3a1a52; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; padding:10px 14px; border:none; }
   .gtablo td { font-size:13px; padding:11px 14px; vertical-align:middle; border-top:1px solid #ece6f3; }
   .gtablo tr:hover td { background:#fbfafd; }

   .nps-rozet { padding:3px 9px; border-radius:11px; font-size:11px; font-weight:700; color:#fff; }
   .nps-rozet.detractor { background:#ef4444; }
   .nps-rozet.passive   { background:#f59e0b; }
   .nps-rozet.promoter  { background:#10b981; }

   .yildizgrup { color:#f59e0b; font-size:14px; letter-spacing:1px; }
   .yildizgrup .bos { color:#d8d2e0; }

   .filtre-bar { display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap; padding:14px 16px; background:#fbfafd; border:1px solid #ece6f3; border-radius:10px; margin-bottom:14px; }
   .filtre-bar label { font-size:11px; color:#5C008E; font-weight:700; text-transform:uppercase; letter-spacing:.4px; margin-bottom:4px; display:block; }
   .filtre-bar input { border:1.5px solid #dfd6ea; border-radius:7px; font-size:13px; padding:7px 10px; min-height:34px; }

   .btn-mor { background:#5C008E; color:#fff; border:none; padding:8px 16px; border-radius:7px; font-size:13px; font-weight:600; }
   .btn-mor:hover { background:#48006e; color:#fff; }
   .btn-mor-out { background:transparent; color:#5C008E; border:1.5px solid #5C008E; padding:6px 12px; border-radius:7px; font-size:12px; font-weight:600; cursor:pointer; }
   .btn-mor-out:hover { background:#5C008E; color:#fff; }

   #detayModal { z-index:10550; }
   #detayModal .modal-dialog { max-width:680px; margin:auto; }
   #detayModal .modal-content { border-radius:14px; border:none; box-shadow:0 18px 50px rgba(92,0,142,.18); }
   #detayModal .modal-header { background:#faf5ff; border-bottom:1px solid #ece6f3; padding:14px 22px; border-radius:14px 14px 0 0; }
   #detayModal .modal-header h4 { color:#3a1a52; font-size:17px; font-weight:700; margin:0; }
   #detayModal .modal-body { padding:18px 22px; max-height:78vh; overflow-y:auto; }

   .cevap-blok { background:#fbfafd; border:1px solid #ece6f3; border-radius:8px; padding:11px 14px; margin-bottom:9px; }
   .cevap-blok .soru { font-size:12.5px; color:#5C008E; font-weight:600; margin-bottom:4px; }
   .cevap-blok .deg  { font-size:13.5px; color:#2d1b3f; }
</style>

<div class="page-header">
   <div class="row">
      <div class="col-md-6 col-sm-12">
         <div class="title"><h1>{{$sayfa_baslik}}</h1></div>
         <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{$sayfa_baslik}}</li>
         </ol></nav>
      </div>
      <div class="col-md-6 col-sm-12 text-right">
         <a href="/isletmeyonetim/anket-sablonlari?sube={{$isletme->id}}" class="btn-mor-out" style="text-decoration:none; display:inline-block;">
            <i class="fa fa-cogs"></i> Şablonları Yönet
         </a>
      </div>
   </div>
</div>

{{-- PREMIUM: Reputation Booster Kartı --}}
@php
   $premiumAktif = isset($salon->reputation_premium_aktif) && $salon->reputation_premium_aktif;
   $googleKurulu = isset($salon->google_review_url) && $salon->google_review_url;
@endphp

<div class="akt-card mb-3" style="background:linear-gradient(135deg, #fff 0%, #faf5ff 100%); border:1px solid #ece6f3; padding:18px 22px;">
   <div style="display:flex; gap:14px; align-items:flex-start; flex-wrap:wrap;">
      <div style="width:46px; height:46px; background:linear-gradient(135deg,#4285F4,#1A73E8); border-radius:11px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:20px; flex-shrink:0;">
         <i class="fa fa-star"></i>
      </div>
      <div style="flex:1; min-width:240px;">
         <h5 style="margin:0; color:#3a1a52; font-size:15px; font-weight:700;">
            Reputation Booster
            @if($premiumAktif)
               <span style="background:#10b981; color:#fff; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700; margin-left:6px;">PREMIUM AKTİF</span>
            @else
               <span style="background:#94a3b8; color:#fff; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700; margin-left:6px;">PASİF</span>
            @endif
         </h5>
         <p style="margin:4px 0 0; color:#5b6770; font-size:12.5px; line-height:1.55;">
            Yüksek puan veren müşterileri otomatik <b>Google Review'a yönlendir</b>, düşük puanları içeride yakala (anlık SMS uyarısı).
         </p>
      </div>
      <div style="display:flex; gap:8px; flex-shrink:0;">
         @if($premiumAktif)
            <button type="button" class="btn-mor-out" onclick="googleAyarAc()"><i class="fa fa-cog"></i> Ayarla</button>
         @else
            <button type="button" id="reputationPremiumAcBtn" class="btn-mor" style="background:linear-gradient(135deg,#4285F4,#1A73E8);" onclick="reputationPremiumAc()">
               <i class="fa fa-rocket"></i> Premium Aç
            </button>
         @endif
      </div>
   </div>

   @if($premiumAktif)
   <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(170px, 1fr)); gap:10px; margin-top:14px; padding-top:14px; border-top:1px dashed #ece6f3;">
      <div style="background:#fff; border:1px solid #ece6f3; border-radius:8px; padding:10px 14px;">
         <div style="font-size:10.5px; color:#8a8295; font-weight:600; text-transform:uppercase; letter-spacing:.4px;">Google Bağlantı</div>
         <div style="font-size:13px; font-weight:700; margin-top:3px;">
            @if($googleKurulu)
               <span style="color:#10b981;"><i class="fa fa-check-circle"></i> Kurulu</span>
            @else
               <span style="color:#ef4444;"><i class="fa fa-times-circle"></i> Eksik</span>
            @endif
         </div>
      </div>
      <div style="background:#fff; border:1px solid #ece6f3; border-radius:8px; padding:10px 14px;">
         <div style="font-size:10.5px; color:#8a8295; font-weight:600; text-transform:uppercase; letter-spacing:.4px;">Google'a Yönlendirilen</div>
         <div style="font-size:18px; font-weight:800; color:#1A73E8; margin-top:1px;">{{ $google_tiklamalar }}</div>
      </div>
      <div style="background:#fff; border:1px solid #ece6f3; border-radius:8px; padding:10px 14px;">
         <div style="font-size:10.5px; color:#8a8295; font-weight:600; text-transform:uppercase; letter-spacing:.4px;">Düşük Puan Uyarısı</div>
         <div style="font-size:18px; font-weight:800; color:#ef4444; margin-top:1px;">{{ $kotu_puan_uyarilari }}</div>
      </div>
      <div style="background:#fff; border:1px solid #ece6f3; border-radius:8px; padding:10px 14px;">
         <div style="font-size:10.5px; color:#8a8295; font-weight:600; text-transform:uppercase; letter-spacing:.4px;">Uyarı Telefonu</div>
         <div style="font-size:13px; font-weight:700; margin-top:3px; color:{{ $salon->kotu_puan_uyari_telefon ? '#10b981' : '#ef4444' }};">
            {{ $salon->kotu_puan_uyari_telefon ?: 'Tanımsız' }}
         </div>
      </div>
   </div>
   @endif
</div>

{{-- Google Review Ayar Modal --}}
<div id="googleAyarModal" class="modal fade" tabindex="-1">
   <div class="modal-dialog" style="max-width:560px; margin:auto;">
      <div class="modal-content" style="border-radius:14px; border:none; box-shadow:0 18px 50px rgba(92,0,142,.18);">
         <div class="modal-header" style="background:linear-gradient(135deg,#4285F4,#1A73E8); color:#fff; padding:14px 22px; border-radius:14px 14px 0 0;">
            <h4 style="color:#fff; font-size:17px; font-weight:700; margin:0; display:flex; align-items:center; gap:10px;">
               <span style="width:34px; height:34px; background:#fff; color:#1A73E8; border-radius:8px; display:flex; align-items:center; justify-content:center; font-weight:900;">G</span>
               Google Review Ayarları
            </h4>
            <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:.9;">×</button>
         </div>
         <div class="modal-body" style="padding:18px 22px;">
            <div style="background:#eef4ff; border-left:4px solid #1A73E8; padding:11px 14px; border-radius:6px; margin-bottom:14px; font-size:12.5px; color:#1e3a8a; line-height:1.55;">
               <b>📍 Google Maps URL'sini yapıştırın</b><br>
               1. Telefonunda Google Maps'i aç<br>
               2. Salonunu ara, "Paylaş" → "Bağlantı kopyala"<br>
               3. Aşağıya yapıştır
            </div>

            <div style="margin-bottom:5px; font-size:12px; color:#5C008E; font-weight:700; text-transform:uppercase; letter-spacing:.3px;">Google Maps URL veya Place ID</div>
            <input type="text" id="googleInput" class="form-control" placeholder="https://g.page/r/... veya https://maps.app.goo.gl/..." value="{{ $salon->google_review_url ?? '' }}" style="border:1.5px solid #dfd6ea; border-radius:7px; font-size:13.5px; padding:8px 11px; min-height:36px; margin-bottom:14px;">

            <hr style="margin:14px 0; border-top:1px solid #ece6f3;">

            <div style="margin-bottom:8px; font-size:13px; font-weight:700; color:#10b981;">⭐ Yüksek Puan → Google'a Yönlendir</div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
               <div>
                  <div style="font-size:11px; color:#5b6770; font-weight:600; margin-bottom:3px;">NPS eşiği (üstü Google'a)</div>
                  <input type="number" id="esikNps" class="form-control" min="0" max="10" value="{{ $salon->google_review_esik_nps ?? 9 }}" style="border:1.5px solid #dfd6ea; border-radius:7px; font-size:13px; padding:6px 9px; min-height:32px;">
               </div>
               <div>
                  <div style="font-size:11px; color:#5b6770; font-weight:600; margin-bottom:3px;">Yıldız eşiği (üstü Google'a)</div>
                  <input type="number" id="esikCsat" class="form-control" min="1" max="5" step="0.1" value="{{ $salon->google_review_esik_csat ?? 4.5 }}" style="border:1.5px solid #dfd6ea; border-radius:7px; font-size:13px; padding:6px 9px; min-height:32px;">
               </div>
            </div>

            <div style="margin:14px 0 8px; font-size:13px; font-weight:700; color:#ef4444;">🚨 Düşük Puan → Anlık SMS Uyarısı</div>
            <div style="margin-bottom:5px; font-size:11px; color:#5b6770; font-weight:600;">Uyarı SMS'i gidecek telefon (10 hane, salon yetkilisi)</div>
            <input type="text" id="uyariTel" class="form-control" placeholder="5XXXXXXXXX" maxlength="10" value="{{ $salon->kotu_puan_uyari_telefon ?? '' }}" style="border:1.5px solid #dfd6ea; border-radius:7px; font-size:13px; padding:6px 9px; min-height:32px; margin-bottom:10px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
               <div>
                  <div style="font-size:11px; color:#5b6770; font-weight:600; margin-bottom:3px;">NPS eşiği (altı uyarı)</div>
                  <input type="number" id="uyariEsikNps" class="form-control" min="0" max="10" value="{{ $salon->kotu_puan_uyari_esik_nps ?? 6 }}" style="border:1.5px solid #dfd6ea; border-radius:7px; font-size:13px; padding:6px 9px; min-height:32px;">
               </div>
               <div>
                  <div style="font-size:11px; color:#5b6770; font-weight:600; margin-bottom:3px;">Yıldız eşiği (altı uyarı)</div>
                  <input type="number" id="uyariEsikCsat" class="form-control" min="1" max="5" step="0.1" value="{{ $salon->kotu_puan_uyari_esik_csat ?? 2.5 }}" style="border:1.5px solid #dfd6ea; border-radius:7px; font-size:13px; padding:6px 9px; min-height:32px;">
               </div>
            </div>
         </div>
         <div class="modal-footer" style="padding:12px 22px; border-top:1px solid #ece6f3;">
            <button type="button" class="btn-mor-out" data-dismiss="modal">İptal</button>
            <button type="button" class="btn-mor" id="googleKaydetBtn" style="background:linear-gradient(135deg,#4285F4,#1A73E8);" onclick="googleAyarKaydet()"><i class="fa fa-save"></i> Kaydet</button>
         </div>
      </div>
   </div>
</div>

<form method="GET" action="" class="filtre-bar">
   <input type="hidden" name="sube" value="{{$isletme->id}}">
   <div>
      <label>Başlangıç</label>
      <input type="date" name="bas" value="{{$bas}}">
   </div>
   <div>
      <label>Bitiş</label>
      <input type="date" name="bit" value="{{$bit}}">
   </div>
   <div>
      <button type="submit" class="btn-mor"><i class="fa fa-search"></i> Filtrele</button>
   </div>
</form>

<div class="istat-grid">
   <div class="istat-kart">
      <div class="et">Gönderim</div>
      <div class="deg">{{ $istatistik['toplam_gonderim'] }}</div>
      <div class="alt-deg">SMS / WhatsApp</div>
   </div>
   <div class="istat-kart">
      <div class="et">Cevaplanan</div>
      <div class="deg">{{ $istatistik['toplam_cevap'] }}</div>
      <div class="alt-deg">%{{ $istatistik['cevap_orani'] }} cevap oranı</div>
   </div>
   <div class="istat-kart nps">
      <div class="et">NPS Skoru</div>
      <div class="deg">{{ $istatistik['nps_skor'] !== null ? $istatistik['nps_skor'] : '—' }}</div>
      <div class="alt-deg">Tavsiye Edilirlik Skoru</div>
   </div>
   <div class="istat-kart csat">
      <div class="et">Memnuniyet</div>
      <div class="deg">{{ $istatistik['csat_ortalama'] !== null ? $istatistik['csat_ortalama'] : '—' }}<span style="font-size:14px; color:#8a8295;"> / 5</span></div>
      <div class="alt-deg">Yıldız ortalaması</div>
   </div>
</div>

@php
   $totalNps = $istatistik['nps_promoter'] + $istatistik['nps_passive'] + $istatistik['nps_detractor'];
   $detPct = $totalNps ? round($istatistik['nps_detractor']*100/$totalNps) : 0;
   $pasPct = $totalNps ? round($istatistik['nps_passive']*100/$totalNps) : 0;
   $proPct = $totalNps ? 100 - $detPct - $pasPct : 0;
@endphp

@if($totalNps > 0)
<div class="akt-card mb-3">
   <div style="display:flex; justify-content:space-between; align-items:center;">
      <div>
         <div style="font-size:12px; color:#5C008E; font-weight:700; text-transform:uppercase; letter-spacing:.4px;">NPS Dağılımı</div>
         <div style="font-size:11.5px; color:#8a8295; margin-top:2px;">Toplam {{ $totalNps }} NPS cevabı</div>
      </div>
   </div>
   <div class="nps-bar-wrap">
      @if($detPct > 0)<div class="nps-bar-detractor" style="width:{{$detPct}}%;">{{$detPct}}%</div>@endif
      @if($pasPct > 0)<div class="nps-bar-passive" style="width:{{$pasPct}}%;">{{$pasPct}}%</div>@endif
      @if($proPct > 0)<div class="nps-bar-promoter" style="width:{{$proPct}}%;">{{$proPct}}%</div>@endif
   </div>
   <div class="nps-legend">
      <div><span class="renk" style="background:#ef4444;"></span> Detraktör (0-6): <b>{{ $istatistik['nps_detractor'] }}</b></div>
      <div><span class="renk" style="background:#f59e0b;"></span> Pasif (7-8): <b>{{ $istatistik['nps_passive'] }}</b></div>
      <div><span class="renk" style="background:#10b981;"></span> Tavsiyeci (9-10): <b>{{ $istatistik['nps_promoter'] }}</b></div>
   </div>
</div>
@endif

<div class="akt-card" style="padding:0; overflow:hidden;">
   <div style="padding:14px 20px; border-bottom:1px solid #ece6f3;">
      <h5 style="margin:0; font-size:14px; color:#3a1a52; font-weight:700;">
         <i class="fa fa-list" style="color:#5C008E;"></i> Anket Cevapları
      </h5>
   </div>
   @if(count($gonderimler) === 0)
      <div style="text-align:center; padding:40px 20px; color:#8a8295;">
         <i class="fa fa-inbox" style="font-size:42px; color:#d8d2e0;"></i>
         <p style="margin-top:10px; font-size:13.5px;">Bu tarih aralığında anket gönderimi bulunmuyor.</p>
      </div>
   @else
      <div style="overflow-x:auto;">
         <table class="table gtablo" style="margin:0;">
            <thead>
               <tr>
                  <th>Müşteri</th>
                  <th style="width:160px;">Gönderim</th>
                  <th style="width:80px; text-align:center;">NPS</th>
                  <th style="width:120px; text-align:center;">Memnuniyet</th>
                  <th>Yorum</th>
                  <th style="width:120px; text-align:center;">Durum</th>
                  <th style="width:90px; text-align:right;"></th>
               </tr>
            </thead>
            <tbody>
               @foreach($gonderimler as $g)
                  @php
                     $npsClass = '';
                     if($g->nps_skoru !== null){
                        $npsClass = $g->nps_skoru >= 9 ? 'promoter' : ($g->nps_skoru >= 7 ? 'passive' : 'detractor');
                     }
                  @endphp
                  <tr>
                     <td>
                        <b>{{ $g->ad_soyad ?? '—' }}</b>
                        <div style="font-size:11.5px; color:#8a8295;">{{ $g->telefon }}</div>
                     </td>
                     <td>
                        <div style="font-size:12.5px;">{{ $g->gonderim_zamani ? \Carbon\Carbon::parse($g->gonderim_zamani)->format('d.m.Y H:i') : '—' }}</div>
                        <div style="font-size:11px; color:#8a8295;">
                           <i class="fa fa-{{ $g->gonderim_kanali === 'whatsapp' ? 'whatsapp' : 'mobile' }}"></i> {{ ucfirst($g->gonderim_kanali) }}
                        </div>
                     </td>
                     <td style="text-align:center;">
                        @if($g->nps_skoru !== null)
                           <span class="nps-rozet {{$npsClass}}">{{ $g->nps_skoru }}</span>
                        @else <span style="color:#cbd5e1;">—</span>@endif
                     </td>
                     <td style="text-align:center;">
                        @if($g->csat_skoru !== null)
                           @php $tam = floor($g->csat_skoru); @endphp
                           <span class="yildizgrup">
                              @for($i=1;$i<=5;$i++){{ $i <= $tam ? '★' : '☆' }}@endfor
                           </span>
                           <div style="font-size:11px; color:#8a8295;">{{ $g->csat_skoru }}</div>
                        @else <span style="color:#cbd5e1;">—</span>@endif
                     </td>
                     <td>
                        @if($g->genel_yorum)
                           <div style="font-size:12.5px; color:#3a1a52; max-width:280px;">{{ mb_substr($g->genel_yorum, 0, 100) }}@if(mb_strlen($g->genel_yorum) > 100)…@endif</div>
                        @else <span style="color:#cbd5e1;">—</span>@endif
                     </td>
                     <td style="text-align:center;">
                        @if($g->cevaplandi)
                           <span style="background:#10b981; color:#fff; padding:3px 9px; border-radius:11px; font-size:11px; font-weight:600;">Cevapladı</span>
                        @elseif($g->son_gecerlilik && \Carbon\Carbon::parse($g->son_gecerlilik)->lt(now()))
                           <span style="background:#94a3b8; color:#fff; padding:3px 9px; border-radius:11px; font-size:11px; font-weight:600;">Süre Doldu</span>
                        @else
                           <span style="background:#f59e0b; color:#fff; padding:3px 9px; border-radius:11px; font-size:11px; font-weight:600;">Bekliyor</span>
                        @endif
                     </td>
                     <td style="text-align:right;">
                        @if($g->cevaplandi)
                           <button class="btn-mor-out" onclick="detayGoster({{$g->id}})"><i class="fa fa-eye"></i> Detay</button>
                        @endif
                     </td>
                  </tr>
               @endforeach
            </tbody>
         </table>
      </div>
   @endif
</div>

<div id="detayModal" class="modal fade" tabindex="-1">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <h4 id="detayBaslik">Anket Cevap Detayı</h4>
            <button type="button" class="close" data-dismiss="modal">×</button>
         </div>
         <div class="modal-body" id="detayIcerik">
            <div style="text-align:center; padding:30px;"><i class="fa fa-spinner fa-spin" style="font-size:24px; color:#5C008E;"></i></div>
         </div>
      </div>
   </div>
</div>

<script>
function detayGoster(id){
   $('#detayIcerik').html('<div style="text-align:center; padding:30px;"><i class="fa fa-spinner fa-spin" style="font-size:24px; color:#5C008E;"></i></div>');
   $('#detayModal').modal('show');
   $.get('/isletmeyonetim/anket-gonderim-detay?sube={{$isletme->id}}&id='+id, function(resp){
      if(resp.hata){ $('#detayIcerik').html('<p>Detay bulunamadı.</p>'); return; }
      var html = '<div style="margin-bottom:14px; padding:11px 14px; background:#faf5ff; border-radius:8px;">'+
                 '<div style="font-size:12px; color:#5C008E; font-weight:700;">'+(resp.musteri_ad||'')+'</div>'+
                 '<div style="font-size:11.5px; color:#8a8295;">Şablon: '+(resp.sablon_ad||'')+'</div>'+
                 '</div>';
      var cevapMap = {};
      (resp.cevaplar||[]).forEach(function(c){ cevapMap[c.indeks] = c; });
      (resp.sorular||[]).forEach(function(s, idx){
         if(['bolum_basligi','bilgi_metni'].indexOf(s.tip) !== -1) return;
         var c = cevapMap[idx];
         var deg = c ? c.cevap : '—';
         if(s.tip === 'csat_yildiz'){
            var n = parseInt(deg)||0;
            var yld = ''; for(var i=1;i<=5;i++) yld += i<=n ? '★' : '☆';
            deg = '<span style="color:#f59e0b; font-size:18px;">'+yld+'</span> <span style="color:#8a8295; font-size:12px;">('+n+'/5)</span>';
         } else if(s.tip === 'nps'){
            var n = parseInt(deg);
            var renk = n>=9 ? '#10b981' : (n>=7 ? '#f59e0b' : '#ef4444');
            deg = '<span style="background:'+renk+'; color:#fff; padding:3px 11px; border-radius:11px; font-weight:700;">'+n+'</span> <span style="color:#8a8295; font-size:12px;">/ 10</span>';
         } else if(s.tip === 'evet_hayir'){
            deg = deg === 'evet' ? '<span style="color:#10b981; font-weight:600;">✓ Evet</span>' : (deg==='hayir' ? '<span style="color:#ef4444; font-weight:600;">✗ Hayır</span>' : '—');
         } else if(s.tip === 'cok_secim' && Array.isArray(deg)){
            deg = deg.join(', ');
         } else if(typeof deg === 'string'){
            deg = deg.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
         }
         html += '<div class="cevap-blok">'+
                    '<div class="soru">'+(idx+1)+'. '+s.soru+'</div>'+
                    '<div class="deg">'+deg+'</div>'+
                 '</div>';
      });
      $('#detayIcerik').html(html);
   });
}

$(document).on('show.bs.modal', '#detayModal', function(){ $(this).appendTo('body'); });
$(document).on('show.bs.modal', '#googleAyarModal', function(){ $(this).appendTo('body'); });

function googleAyarAc(){
   $('#googleAyarModal').modal('show');
}

function reputationPremiumAc(){
   var swalAvailable = (typeof swal === 'function');
   var dogrula = function(){
      var btn = document.getElementById('reputationPremiumAcBtn');
      if(btn){ btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Açılıyor...'; }
      $.post('/isletmeyonetim/reputation-premium-ac', {
         _token: '{{csrf_token()}}',
         sube: {{$isletme->id}}
      }, function(resp){
         if(resp && resp.basarili){
            if(swalAvailable){
               swal({title:'Premium aktif edildi', type:'success', timer:1500, showConfirmButton:false})
                  .then(function(){ location.reload(); }).catch(function(){ location.reload(); });
            } else {
               alert('Reputation Booster Premium aktif edildi.');
               location.reload();
            }
         } else {
            if(btn){ btn.disabled = false; btn.innerHTML = '<i class="fa fa-rocket"></i> Premium Aç'; }
            var msg = (resp && resp.mesaj) ? resp.mesaj : 'Açılamadı';
            if(swalAvailable){ swal({title:'Hata', text: msg, type:'error'}); }
            else { alert('Hata: '+msg); }
         }
      }).fail(function(){
         if(btn){ btn.disabled = false; btn.innerHTML = '<i class="fa fa-rocket"></i> Premium Aç'; }
         if(swalAvailable){ swal({title:'Hata', text:'Sunucu hatası', type:'error'}); }
         else { alert('Sunucu hatası.'); }
      });
   };

   if(swalAvailable){
      swal({
         title: 'Reputation Booster Premium',
         text:  'Premium özellikleri bu salon için aktif edilsin mi? (Google Review yönlendirme + düşük puan SMS uyarısı)',
         type:  'question',
         showCancelButton: true,
         confirmButtonText: 'Evet, aç',
         cancelButtonText:  'Vazgeç',
         confirmButtonClass:'btn btn-primary'
      }).then(function(r){ if(r && r.value) dogrula(); });
   } else {
      if(confirm('Reputation Booster Premium özellikleri aktif edilsin mi?')) dogrula();
   }
}

function googleAyarKaydet(){
   var btn = document.getElementById('googleKaydetBtn');
   btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Kaydediliyor...';
   $.post('/isletmeyonetim/google-review-kaydet', {
      _token: '{{csrf_token()}}',
      sube: {{$isletme->id}},
      google_input: document.getElementById('googleInput').value,
      google_review_esik_nps: document.getElementById('esikNps').value,
      google_review_esik_csat: document.getElementById('esikCsat').value,
      kotu_puan_uyari_telefon: document.getElementById('uyariTel').value,
      kotu_puan_uyari_esik_nps: document.getElementById('uyariEsikNps').value,
      kotu_puan_uyari_esik_csat: document.getElementById('uyariEsikCsat').value
   }, function(resp){
      btn.disabled = false; btn.innerHTML = '<i class="fa fa-save"></i> Kaydet';
      if(resp.basarili){
         alert('Google Review ayarları kaydedildi.' + (resp.review_url ? '\n\nÜretilen link:\n'+resp.review_url : ''));
         location.reload();
      } else alert('Hata: '+(resp.mesaj||''));
   }).fail(function(){
      btn.disabled = false; btn.innerHTML = '<i class="fa fa-save"></i> Kaydet';
      alert('Sunucu hatası.');
   });
}
</script>
@endsection
