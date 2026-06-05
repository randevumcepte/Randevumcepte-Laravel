@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<script>
    // V2 (modern) randevu modali artik standart akis. Slot tiklamalari da
    // v2'ye yonlendirilir (modal-view-event-add-v2.blade.php intercept'i).
    window.useV2Modal = true;
</script>
<style>
   /* V1 modali tamamen gizle — v2 dependency'leri (window.randevuModalData,
      hizmetDataCache, paketKontrolu vs.) icin DOM'da kalmasi gerekiyor ama
      kullaniciya GORUNMEMELI. Intercept v1'in show'unu yakaliyor zaten;
      bu CSS son guvenlik perdesi. */
   #modal-view-event-add { display: none !important; }
</style>
<style>
   /* Randevu detay popup gorunumu (eskiden her event icin partial'dan tekrar tekrar gomuluyordu, sayfaya bir kez tasindi) */
   .rd-detail { font-size:13.5px; color:#3a2e57; margin:-10px -15px; }
   .rd-detail .rd-row { display:flex; align-items:flex-start; padding:9px 14px; border-bottom:1px solid #f1ecf7; gap:10px; }
   .rd-detail .rd-row:last-child { border-bottom:0; }
   .rd-detail .rd-row:nth-child(odd) { background:#fbfafd; }
   .rd-detail .rd-label { flex:0 0 160px; color:#7c6c8a; font-weight:600; font-size:12.5px; display:flex; align-items:center; gap:6px; }
   .rd-detail .rd-label i { color:#5C008E; opacity:.75; width:14px; text-align:center; }
   .rd-detail .rd-value { flex:1; color:#2d2143; font-weight:500; word-break:break-word; }
   .rd-detail .rd-value.empty { color:#bcb3c9; font-style:italic; font-weight:400; }
   .rd-status { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11.5px; font-weight:700; }
   .rd-status.beklemede { background:#fff4e0; color:#a86200; }
   .rd-status.basarili  { background:#e6f9ed; color:#0c7a3a; }
   .rd-status.iptal     { background:#fdecec; color:#c81e1e; }
   .rd-status.geldi     { background:#e6f9ed; color:#0c7a3a; }
   .rd-status.gelmedi   { background:#fdecec; color:#c81e1e; }
   .rdb-row { display:flex; gap:8px; flex-wrap:wrap; width:100%; }
   .rdb-row .btn { flex: 1 1 130px; min-width: 0; border-radius: 8px; font-weight: 600; font-size: 13px; padding: 9px 12px; line-height: 1.2; white-space: normal; }
   .rdb-row .btn i { margin-right: 4px; }
   .rdb-row .rdb-pull-right { margin-left: auto; flex-grow: 0; }
   /* === Modal/Popup z-index HIERARSI (akis sirasiyla katmanlar) === */
   /* swal default toast (99999) | detay 100001 | ekle 100002 | duzenle 100003
      | dropdowns 100015 | paket secim child modal 100020
      | confirmation swals 100030 (HER zaman en ust, modal akisindan tetiklenen) */

   /* Dropdownlar modal'in uzerinde acilsin */
   body > .select2-container--open { z-index: 100015 !important; }
   body > .ts-dropdown { z-index: 100015 !important; }

   /* Musteri Paket/Hizmetleri (ekle modaldan acilan child modal) ekle/duzenle uzerinde */
   #softPaketSecimModal { z-index: 100020 !important; }
   /* NOT: modal-backdrop'lar default Bootstrap z-index'inde (1040) kalmali — modal'in altinda
      olusunlar. Onceki "last-of-type 100019" kurali ekle modali backdrop'un arkasinda
      birakiyordu (modal 100002 < backdrop 100019). Kaldirildi. */

   /* Confirmation/diyalog swal'lari HER zaman en ustte (iptal/sil/onay popup'lari modal arkasinda kalmasin) */
   .sweet-overlay { z-index: 100029 !important; }
   .sweet-alert   { z-index: 100030 !important; }
   .swal2-container { z-index: 100030 !important; }
</style>
<div class="rc-rt-page">

   {{-- Modern Page Header (başlık + filtreler + aksiyonlar TEK satırda) --}}
   <div class="rc-rt-header rc-rt-header--merged">
      <div class="rc-rt-header-left">
         <div class="rc-rt-title-row">
            <div class="rc-rt-icon-bubble"><i class="fa fa-calendar"></i></div>
            <div>
               <h1 class="rc-rt-title">{{$sayfa_baslik}}</h1>
               <nav class="rc-rt-breadcrumb" aria-label="breadcrumb">
                  <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
                  <span class="rc-rt-sep">›</span>
                  <span class="rc-rt-active">{{$sayfa_baslik}}</span>
               </nav>
            </div>
         </div>
      </div>

      <div class="rc-rt-header-filters">
         @if(Auth::guard('satisortakligi')->check() || ( Auth::guard('isletmeyonetim')->check() && !Auth::guard('isletmeyonetim')->user()->hasRole('Personel')))
         <div class="rc-rt-field">
         @else
         <div class="rc-rt-field" style="display:none">
         @endif
            <label for="randevu_ayarina_gore">Görünüm</label>
            <select class="form-control rc-rt-select" id="randevu_ayarina_gore">
               <option {{($isletme->randevu_takvim_turu==1) ? 'selected' : ''}} value="1">Personele Göre</option>
               <option {{($isletme->randevu_takvim_turu==0) ? 'selected' : ''}} value="0">Hizmete Göre</option>
               <option {{($isletme->randevu_takvim_turu==2) ? 'selected' : ''}} value="2">Cihaza Göre</option>
               <option {{($isletme->randevu_takvim_turu==3) ? 'selected' : ''}} value="3">Odaya Göre</option>
            </select>
         </div>

         <div class="rc-rt-field">
            <label for="takvim_tarihe_gore">Tarih</label>
            <input type="text" class="form-control rc-rt-input calendardatepicker" autocomplete="off" id="takvim_tarihe_gore" placeholder="Tarih Seçiniz">
         </div>
      </div>

      <div class="rc-rt-header-right">
         <div class="rc-rt-count-pill randevu-count-button">
            <i class="fa fa-list-ul"></i>
            <span class="rc-rt-count-label">Toplam Randevu</span>
            <span class="rc-rt-count-value">{{$randevular['randevu_sayisi']}}</span>
         </div>
         @yetki('randevu.olustur')
         <a href="#" data-toggle="modal" data-target="#modal-view-event-add-v2"
            class="rc-rt-btn rc-rt-btn-primary yenieklebuton">
            <i class="fa fa-plus"></i><span>Yeni Randevu</span>
         </a>
         @endyetki
      </div>
   </div>

   {{-- Aktif gap kampanyalari bilgi seridi --}}
   @if(!empty($gapKampanyalari))
   <div class="gap-info-strip">
      <span class="gap-strip-label"><i class="fa fa-tag"></i> Aktif Kampanya:</span>
      @foreach($gapKampanyalari as $k)
        <span class="gap-chip gap-{{ $k['gapKey'] }}" title="{{ $k['gapLabel'] }} Kampanyası — %{{ $k['discount'] }} indirim">
          <span class="gap-chip-dot" style="background:{{ $k['color'] }}"></span>
          <span class="gap-chip-time">{{ $k['gapLabel'] }} {{ sprintf('%02d:00-%02d:00', $k['startHour'], $k['endHour']) }}</span>
          <span class="gap-chip-disc">%{{ $k['discount'] }}</span>
        </span>
      @endforeach
   </div>
   @endif

   {{-- Takvim Kartı --}}
   <div class="rc-rt-card rc-rt-calendar-card">
      <div style="position:relative; width:100%; overflow-y:auto">
         <div class="calendar-wrap">
            <div id="calendar"></div>
         </div>
      </div>
   </div>

</div>
<div id="hata"></div>

<style>
/* =================================================================
   RANDEVU TAKVIMI — MODERN KARTLI SISTEM
   Markaya uygun mor (#5C008E / #9D5DC8 / #d946ef)
   ================================================================= */
.rc-rt-page {
   --rc-purple-dark: #5C008E;
   --rc-purple: #9D5DC8;
   --rc-purple-light: #f5eefe;
   --rc-purple-soft: #ead4ff;
   --rc-pink: #d946ef;
   --rc-text: #1f2937;
   --rc-text-soft: #6b7280;
   --rc-border: #eef0f4;
}

/* === HEADER === */
.rc-rt-header {
   display: flex;
   align-items: center;
   justify-content: space-between;
   gap: 16px;
   flex-wrap: wrap;
   padding: 18px 22px;
   margin-bottom: 18px;
   background: #fff;
   border-radius: 14px;
   box-shadow: 0 1px 3px rgba(17, 24, 39, .04), 0 4px 16px rgba(92, 0, 142, .04);
}
.rc-rt-header-left,
.rc-rt-header-right {
   display: flex;
   align-items: center;
   gap: 10px;
   flex-wrap: wrap;
}
/* Tek satira birlesik header: ortadaki filtre bolumu */
.rc-rt-header--merged { gap: 18px 24px; }
.rc-rt-header-left { flex: 0 1 auto; }
.rc-rt-header-filters {
   display: flex;
   align-items: center;
   gap: 16px;
   flex: 1 1 auto;
   justify-content: center;
   min-width: 280px;
}
.rc-rt-header-filters .rc-rt-field { flex: 1 1 200px; }
.rc-rt-header-filters .rc-rt-select,
.rc-rt-header-filters .rc-rt-input { min-width: 130px; }
.rc-rt-header-right { flex: 0 1 auto; }
.rc-rt-title-row {
   display: flex;
   align-items: center;
   gap: 14px;
}
.rc-rt-icon-bubble {
   width: 46px; height: 46px;
   border-radius: 12px;
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%);
   color: #fff;
   display: inline-flex;
   align-items: center;
   justify-content: center;
   font-size: 18px;
   box-shadow: 0 6px 18px rgba(92, 0, 142, .25);
   flex-shrink: 0;
}
.rc-rt-title {
   margin: 0;
   font-size: 19px;
   font-weight: 700;
   color: var(--rc-text);
   line-height: 1.2;
}
.rc-rt-breadcrumb {
   margin-top: 4px;
   font-size: 12.5px;
   color: var(--rc-text-soft);
   display: flex;
   align-items: center;
   gap: 6px;
   flex-wrap: wrap;
}
.rc-rt-breadcrumb a {
   color: var(--rc-text-soft);
   text-decoration: none;
   transition: color .15s;
}
.rc-rt-breadcrumb a:hover { color: var(--rc-purple-dark); }
.rc-rt-breadcrumb .rc-rt-sep { color: #cbd5e1; }
.rc-rt-breadcrumb .rc-rt-active { color: var(--rc-purple-dark); font-weight: 600; }

/* === TOPLAM RANDEVU PILL === */
.rc-rt-count-pill {
   display: inline-flex;
   align-items: center;
   gap: 8px;
   height: 42px;
   padding: 0 16px;
   background: var(--rc-purple-light);
   border: 1px solid var(--rc-purple-soft);
   border-radius: 999px;
   font-size: 13px;
   font-weight: 600;
   color: var(--rc-purple-dark);
   white-space: nowrap;
   cursor: pointer;
   transition: background .15s;
}
.rc-rt-count-pill:hover { background: var(--rc-purple-soft); }
.rc-rt-count-pill i { font-size: 13px; opacity: .8; }
.rc-rt-count-pill .rc-rt-count-label { font-weight: 600; }
.rc-rt-count-pill .rc-rt-count-value {
   display: inline-flex;
   align-items: center;
   justify-content: center;
   min-width: 28px;
   height: 26px;
   padding: 0 9px;
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%);
   color: #fff;
   border-radius: 999px;
   font-size: 12.5px;
   font-weight: 800;
   box-shadow: 0 2px 6px rgba(92, 0, 142, .25);
}

/* === HEADER BUTONLAR === */
.rc-rt-btn {
   display: inline-flex;
   align-items: center;
   gap: 8px;
   height: 42px;
   padding: 0 18px;
   border-radius: 999px;
   font-size: 13.5px;
   font-weight: 600;
   color: #fff !important;
   text-decoration: none !important;
   border: none;
   white-space: nowrap;
   cursor: pointer;
   transition: transform .15s, box-shadow .15s, filter .15s;
}
.rc-rt-btn i { font-size: 14px; }
.rc-rt-btn:hover { transform: translateY(-1px); filter: brightness(1.06); color: #fff; }
.rc-rt-btn:active { transform: translateY(0); }
.rc-rt-btn-primary {
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%);
   box-shadow: 0 6px 16px rgba(92, 0, 142, .28);
}

/* === FİLTRE ALANLARI (header içinde) === */
.rc-rt-field {
   display: flex;
   flex-direction: row;
   align-items: center;
   gap: 10px;
   min-width: 0;
}
.rc-rt-field label {
   font-size: 11.5px;
   font-weight: 700;
   text-transform: uppercase;
   letter-spacing: .04em;
   color: var(--rc-text-soft);
   margin: 0;
   white-space: nowrap;
   flex-shrink: 0;
}
.rc-rt-select,
.rc-rt-input {
   height: 40px !important;
   border: 1px solid var(--rc-border) !important;
   border-radius: 10px !important;
   padding: 0 14px !important;
   font-size: 13.5px !important;
   color: var(--rc-text) !important;
   background-color: #fafbfc !important;
   transition: border-color .15s, box-shadow .15s, background-color .15s;
   width: 100%;
   appearance: none;
   -webkit-appearance: none;
   -moz-appearance: none;
   background-repeat: no-repeat;
   background-position: right 12px center;
   background-size: 12px;
}
.rc-rt-select {
   background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
   padding-right: 36px !important;
}
.rc-rt-select:focus,
.rc-rt-input:focus {
   border-color: var(--rc-purple) !important;
   background-color: #fff !important;
   box-shadow: 0 0 0 4px rgba(157, 93, 200, .12) !important;
   outline: none;
}

/* === TAKVIM KARTI === */
.rc-rt-card {
   background: #fff;
   border-radius: 14px;
   box-shadow: 0 1px 3px rgba(17, 24, 39, .04), 0 4px 16px rgba(92, 0, 142, .04);
   padding: 18px;
   margin-bottom: 30px;
}
.rc-rt-calendar-card .calendar-wrap { margin-top: 0; }

/* === RESPONSIVE === */
@media (max-width: 1024px) {
   .rc-rt-header { padding: 14px 16px; }
   .rc-rt-icon-bubble { width: 40px; height: 40px; font-size: 16px; }
   .rc-rt-title { font-size: 17px; }
   .rc-rt-btn { height: 38px; padding: 0 14px; font-size: 12.5px; }
   .rc-rt-count-pill { height: 38px; font-size: 12.5px; padding: 0 14px; }
   .rc-rt-card { padding: 14px; }
}
@media (max-width: 768px) {
   .rc-rt-header {
      padding: 12px 14px;
      border-radius: 12px;
   }
   .rc-rt-header-left { width: 100%; }
   .rc-rt-header-filters {
      width: 100%;
      flex-wrap: wrap;
      justify-content: stretch;
      gap: 10px;
      min-width: 0;
   }
   .rc-rt-header-filters .rc-rt-field { flex: 1 1 100%; }
   .rc-rt-header-right {
      width: 100%;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 8px;
   }
   .rc-rt-header-right .rc-rt-btn { flex: 1; justify-content: center; }
   .rc-rt-count-pill { flex: 1; justify-content: center; }
   .rc-rt-title { font-size: 16px; }
   .rc-rt-breadcrumb { font-size: 11.5px; }

   .rc-rt-card { padding: 10px; border-radius: 12px; }
}
@media (max-width: 420px) {
   .rc-rt-header-right { flex-direction: column; }
   .rc-rt-header-right .rc-rt-btn,
   .rc-rt-header-right .rc-rt-count-pill { width: 100%; }
   .rc-rt-title-row { gap: 10px; }
   .rc-rt-icon-bubble { width: 36px; height: 36px; font-size: 14px; }
}
</style>

{{-- Gap kampanya gorsel: bilgi seridi + kart rozeti. TAKVIM GRID'INE DOKUNMAZ --}}
<style type="text/css">
  /* Üst bilgi şeridi — takvim wrapper'ın üstünde, FullCalendar'ı etkilemez */
  .gap-info-strip {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 14px;
    margin: 0 0 12px 0;
    background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%);
    border: 1px solid rgba(251, 191, 36, 0.40);
    border-radius: 12px;
    flex-wrap: wrap;
    font-size: 13px;
    box-shadow: 0 2px 6px rgba(251, 191, 36, 0.08);
  }
  .gap-info-strip .gap-strip-label {
    font-weight: 700;
    color: #92400E;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
  }
  .gap-info-strip .gap-strip-label i { color: #D97706; }
  .gap-info-strip .gap-chip {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 5px 10px;
    background: #ffffff;
    border: 1px solid #FCD34D;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    color: #1f2937;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
  }
  .gap-info-strip .gap-chip.gap-morning   { border-color: #F59E0B; }
  .gap-info-strip .gap-chip.gap-afternoon { border-color: #EA580C; }
  .gap-info-strip .gap-chip.gap-evening   { border-color: #7C3AED; }
  .gap-info-strip .gap-chip-dot {
    width: 10px; height: 10px; border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
  }
  .gap-info-strip .gap-chip-time { font-weight: 600; }
  .gap-info-strip .gap-chip-disc {
    background: linear-gradient(135deg, #22C55E, #16A34A);
    color: #fff;
    padding: 2px 9px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: -0.2px;
    box-shadow: 0 1px 2px rgba(22, 163, 74, 0.25);
  }

</style>

@endsection
