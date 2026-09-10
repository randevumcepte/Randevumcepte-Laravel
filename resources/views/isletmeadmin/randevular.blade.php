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

   /* Takvim baslik tarihi (23 Temmuz 2026 Perşembe) tiklanabilir — basinda belirgin takvim ikonu */
   #calendar .fc-center h2.rc-cal-title-clickable { cursor: pointer; }
   #calendar .fc-center h2 .rc-cal-title-ico { margin-right: 9px; color: #5C008E; font-size: .88em; vertical-align: baseline; transition: color .15s ease; }
   #calendar .fc-center h2.rc-cal-title-clickable:hover { color: #5C008E; }
   #calendar .fc-center h2.rc-cal-title-clickable:hover .rc-cal-title-ico { color: #7B2FB8; }
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
         <div class="rc-zoom" title="Takvim büyüklüğü (kalıcı)">
            <button type="button" class="rc-zoom-btn" id="rc-zoom-out" title="Küçült"><i class="fa fa-search-minus"></i></button>
            <span class="rc-zoom-val" id="rc-zoom-val">100%</span>
            <button type="button" class="rc-zoom-btn" id="rc-zoom-in" title="Büyüt"><i class="fa fa-search-plus"></i></button>
         </div>
         <div class="rc-rt-count-pill randevu-count-button">
            <i class="fa fa-list-ul"></i>
            <span class="rc-rt-count-label">Toplam Randevu</span>
            <span class="rc-rt-count-value">{{$randevular['randevu_sayisi']}}</span>
         </div>
         @yetki('randevu.olustur')
         <a href="#" onclick="dersOturumYeni();return false;"
            class="rc-rt-btn yenieklebuton" style="background:#16a085;border-color:#16a085;color:#fff;">
            <i class="fa fa-users"></i><span>Grup Dersi</span>
         </a>
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

{{-- ================= TAKVIM ZOOM (buyutec) — kalici (localStorage) ================= --}}
<style>
.rc-zoom {
   display: inline-flex; align-items: center; gap: 2px;
   height: 42px; padding: 0 6px;
   background: var(--rc-purple-light, #f5eefe);
   border: 1px solid var(--rc-purple-soft, #ead4ff);
   border-radius: 999px;
}
.rc-zoom-btn {
   width: 30px; height: 30px; border: none; background: transparent;
   color: var(--rc-purple-dark, #5C008E); cursor: pointer; border-radius: 50%;
   display: inline-flex; align-items: center; justify-content: center; font-size: 13px;
   transition: background .15s;
}
.rc-zoom-btn:hover { background: var(--rc-purple-soft, #ead4ff); }
.rc-zoom-val {
   min-width: 42px; text-align: center; font-size: 12px; font-weight: 700;
   color: var(--rc-purple-dark, #5C008E); user-select: none;
}
@media (max-width: 1024px){ .rc-zoom { height: 38px; } }
</style>
<script>
(function(){
   // Salon bazli, tarayicida kalici (cikis yapsa bile ayni boyutta acilir)
   var KEY  = 'rc_takvim_zoom_{{ (int)$isletme->id }}';
   var BASE_H = 32.5; // slot yuksekligi = 32.5 * zoom. MAX %400 -> en fazla 130px.
   // Zoom HEM yukseklik HEM genislik olcekler:
   //  - Yukseklik: 25*z  (en buyukte 25*4 = 100px)
   //  - Genislik : 200*z, tavan 200px (custom.js) -> en buyukte 200px, devlesmez
   // Kuculunce (z<1) ikisi de daralir; buyutunce genislik 200'de durur, yukseklik 100'e cikar.
   var MIN  = 0.6, MAX = 4.0, STEP = 0.4;

   function clamp(z){ return Math.min(MAX, Math.max(MIN, Math.round(z*100)/100)); }
   function getZoom(){
      var v = parseFloat(localStorage.getItem(KEY));
      if(isNaN(v)) return 1.0;
      // Eski/araliik disi (or. %300) kayitli deger -> %100'e sifirla (dagilma onlenir).
      if(v < MIN || v > MAX){ v = 1.0; try{ localStorage.setItem(KEY, v); }catch(e){} }
      return v;
   }
   function apply(z, rerender){
      // Zoom faktorunu global yap: kolon GENISLIGINI custom.js'teki eventAfterAllRender
      // bu degere gore hesaplar (250 * z). Boylece zoom HEM yukseklik HEM genislik olcekler.
      window.rcZoom = z;
      var el = document.getElementById('rc-zoom-style');
      if(!el){ el = document.createElement('style'); el.id = 'rc-zoom-style'; document.head.appendChild(el); }
      // Slot YUKSEKLIGI = BASE_H * z (her zaman — kuculunce de daralir)
      var h = Math.round(BASE_H * z);
      el.innerHTML = '#calendar .fc-time-grid .fc-slats td{height:'+h+'px !important;}';
      var lbl = document.getElementById('rc-zoom-val');
      if(lbl) lbl.textContent = Math.round(z*100) + '%';
      if(rerender){
         // Slot yuksekligi degisti. FC ic API'lerini zorlamak (height toggle vs.)
         // randevularin kaybolmasina yol acabiliyor; bu yuzden GUVENLI yol: takvimi
         // uygulamanin KENDI normal render yolundan tam yeniden cizdir. Boylece
         // slatlar yeniden olculur ve event'ler dogru (yeni) yukseklikte cizilir.
         try {
            if(typeof window.takvimyukle === 'function'){
               window.takvimyukle(false, true);   // turdegisti=true -> destroy + tam re-render
            } else if(window.jQuery && jQuery('#calendar').length && jQuery('#calendar').fullCalendar('getView')){
               jQuery('#calendar').fullCalendar('render');
            }
         } catch(e){}
      }
   }
   function setZoom(z){ z = clamp(z); try{ localStorage.setItem(KEY, z); }catch(e){} apply(z, true); }

   // Ilk uygulama: SADECE CSS yaz (FC ilk render'inda bu slat yuksekligini olcer ve
   // kartlari dogru/buyuk cizer). Burada FC'yi yeniden cizdirmiyoruz -> randevular
   // kaybolmaz; takvim normal akisinda zaten dogru boyutta gelir.
   apply(getZoom(), false);

   jQuery(document).on('click', '#rc-zoom-in',  function(){ setZoom(getZoom() + STEP); });
   jQuery(document).on('click', '#rc-zoom-out', function(){ setZoom(getZoom() - STEP); });
})();
</script>

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
/* Tek satira birlesik header: title | filtreler | aksiyonlar — hepsi tek satir.
   Filtreler BUYUMEZ (sabit kompakt genislik) yoksa TARIH alani genisleyip sag
   bolumu (zoom/sayac/buton) alt satira itiyordu. */
.rc-rt-header--merged { gap: 10px 14px; flex-wrap: nowrap; }
.rc-rt-header-left { flex: 0 1 auto; min-width: 0; }
.rc-rt-header-filters {
   display: flex;
   align-items: center;
   gap: 10px 12px;
   flex: 0 1 auto;
   flex-wrap: wrap;
   min-width: 0;
}
.rc-rt-header-filters .rc-rt-field { flex: 0 0 auto; gap: 7px; }
.rc-rt-header-filters .rc-rt-select { width: 146px !important; min-width: 0; }
.rc-rt-header-filters .rc-rt-input  { width: 132px !important; min-width: 0; }
.rc-rt-header-right { flex: 0 0 auto; margin-left: auto; gap: 8px; }
/* Daralan ekranda (laptop) header tek satir kalsin diye etiketleri kucult */
@media (max-width: 1500px){
   .rc-rt-header-filters .rc-rt-field label { font-size: 10.5px; }
}
/* 1200px altinda tek satira sigmaz -> tasmamasi icin tekrar sarmalansin */
@media (max-width: 1199px){
   .rc-rt-header--merged { flex-wrap: wrap; }
}
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
/* Takvim karti: alttaki bosluk kapansin diye ic/dis bosluklar kisildi. */
.rc-rt-calendar-card { padding: 12px 14px 8px; margin-bottom: 10px; }

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

{{-- ============================================================
     GRUP DERSI (Pilates/kurs) — kapasiteli ders oturumu + katilimci modali
     ============================================================ --}}
<style>
  #ders-oturum-modal .modal-dialog{ width:460px; max-width:94%; }
  #ders-oturum-modal .modal-content{ border:none; border-radius:14px; overflow:hidden; box-shadow:0 12px 40px rgba(0,0,0,.25); }
  #ders-oturum-modal .dm-head{ background:#16a085; color:#fff; padding:16px 20px; display:flex; align-items:center; justify-content:space-between; }
  #ders-oturum-modal .dm-head h4, #ders-oturum-modal .dm-head #ders-modal-baslik{ margin:0; font-size:17px; font-weight:700; color:#fff !important; }
  #ders-oturum-modal .dm-head .dm-close{ background:none; border:none; color:#fff; font-size:22px; line-height:1; cursor:pointer; opacity:.9; }
  #ders-oturum-modal .dm-body{ padding:20px; background:#fff; }
  #ders-oturum-modal .dm-grid{ display:grid; grid-template-columns:1fr 1fr; gap:12px 14px; }
  #ders-oturum-modal .dm-field{ display:flex; flex-direction:column; }
  #ders-oturum-modal .dm-field.dm-full{ grid-column:1 / -1; }
  #ders-oturum-modal .dm-field label{ font-size:12px; font-weight:600; color:#5f6b7a; margin:0 0 5px; }
  #ders-oturum-modal .dm-field input, #ders-oturum-modal .dm-field select{
     height:40px; border:1px solid #d7dde3; border-radius:8px; padding:0 11px; font-size:14px; color:#2c3e50; background:#fff; width:100%; }
  #ders-oturum-modal .dm-field input:focus, #ders-oturum-modal .dm-field select:focus{ outline:none; border-color:#16a085; box-shadow:0 0 0 3px rgba(22,160,133,.15); }
  #ders-oturum-modal .dm-btn{ display:block; width:100%; margin-top:16px; height:44px; border:none; border-radius:9px; background:#16a085; color:#fff; font-size:15px; font-weight:700; cursor:pointer; }
  #ders-oturum-modal .dm-btn:hover{ background:#12876f; }
  #ders-oturum-modal .dm-summary{ display:flex; justify-content:space-between; align-items:flex-start; gap:10px; padding-bottom:12px; margin-bottom:14px; border-bottom:1px solid #eef1f4; }
  #ders-oturum-modal .dm-summary .t{ font-size:16px; font-weight:700; color:#2c3e50; }
  #ders-oturum-modal .dm-summary .d{ font-size:12.5px; color:#7f8c8d; margin-top:3px; }
  #ders-oturum-modal .dm-rozet{ white-space:nowrap; font-size:14px; font-weight:700; color:#fff; border-radius:999px; padding:5px 12px; }
  #ders-oturum-modal .dm-rozet.ok{ background:#16a085; } #ders-oturum-modal .dm-rozet.warn{ background:#e67e22; } #ders-oturum-modal .dm-rozet.full{ background:#c0392b; }
  #ders-oturum-modal .dm-search-wrap{ position:relative; margin-bottom:12px; }
  #ders-oturum-modal #ders-musteri-sonuc{ position:absolute; z-index:20; background:#fff; border:1px solid #e1e6ea; border-radius:8px; width:100%; max-height:210px; overflow:auto; display:none; box-shadow:0 6px 18px rgba(0,0,0,.12); margin-top:3px; }
  #ders-oturum-modal .dm-kat-liste{ list-style:none; margin:0 0 6px; padding:0; }
  #ders-oturum-modal .dm-kat-liste li{ border:1px solid #eef1f4; border-radius:9px; padding:9px 11px; margin-bottom:7px; }
  #ders-oturum-modal .dm-kat-row{ display:flex; justify-content:space-between; align-items:center; gap:8px; }
  #ders-oturum-modal .dm-foot{ display:flex; justify-content:space-between; margin-top:6px; }
  #ders-oturum-modal .dm-mini{ border:1px solid #d7dde3; background:#fff; border-radius:7px; padding:6px 11px; font-size:13px; cursor:pointer; color:#34495e; }
  #ders-oturum-modal .dm-mini.danger{ border-color:#f0b4ac; color:#c0392b; }
  #ders-oturum-modal .dm-durum b{ display:inline-block; padding:3px 8px; border-radius:6px; font-size:11px; cursor:pointer; border:1px solid #d7dde3; color:#7f8c8d; font-weight:600; margin-left:3px; }
  #ders-oturum-modal .dm-durum b.on-r{ background:#2980b9; border-color:#2980b9; color:#fff; }
  #ders-oturum-modal .dm-durum b.on-g{ background:#16a085; border-color:#16a085; color:#fff; }
  #ders-oturum-modal .dm-durum b.on-x{ background:#c0392b; border-color:#c0392b; color:#fff; }
  #ders-oturum-modal .dm-x{ background:none; border:none; color:#c0392b; cursor:pointer; font-size:15px; }
</style>
<div class="modal fade" id="ders-oturum-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="dm-head">
        <h4 id="ders-modal-baslik">👥 Grup Dersi</h4>
        <button type="button" class="dm-close" data-dismiss="modal">&times;</button>
      </div>
      <div class="dm-body">

        {{-- OLUSTUR / DUZENLE FORMU --}}
        <div id="ders-form-alani">
          <div style="background:#eafaf4;border:1px solid #cdeee4;border-radius:9px;padding:10px 12px;margin-bottom:14px;font-size:12.5px;color:#0e6b57;line-height:1.5;">
            <i class="fa fa-info-circle"></i> Kapasiteli ders oturumu oluşturur. Takvimde <b>"Ders 2/3 dolu"</b> olarak görünür; bloğa tıklayıp katılımcıları eklersiniz. <b>Hizmet</b> seçerseniz "Geldi"de paketten seans düşer.
          </div>
          <input type="hidden" id="ders-oturum-id" value="">
          <div class="dm-grid">
            <div class="dm-field dm-full"><label>Ders Tipi</label>
              <input type="text" id="ders-tipi" placeholder="Reformer / Mat / Crossfit / Birebir"></div>
            <div class="dm-field dm-full"><label>Eğitmen</label>
              <select id="ders-personel">
                <option value="">— Eğitmen seçiniz —</option>
                @foreach(($dersPersonelleri ?? []) as $p)<option value="{{ $p->id }}">{{ $p->personel_adi }}</option>@endforeach
              </select></div>
            <div class="dm-field dm-full"><label>Hizmet <small style="color:#95a5a6;font-weight:400;">(paketten seans düşümü için)</small></label>
              <select id="ders-hizmet">
                <option value="">— Hizmet bağlama (paket düşmez) —</option>
                @foreach(($dersHizmetleri ?? []) as $h)<option value="{{ $h->id }}">{{ $h->hizmet_adi }}</option>@endforeach
              </select></div>
            <div class="dm-field dm-full"><label>Tarih</label>
              <input type="text" id="ders-tarih" readonly style="cursor:pointer;background:#fff;"></div>
            <div class="dm-field"><label>Başlangıç</label>
              <input type="time" id="ders-saat" value="09:00"></div>
            <div class="dm-field"><label>Bitiş</label>
              <input type="time" id="ders-saat-bitis" value="10:00"></div>
            <div class="dm-field dm-full"><label>Kapasite (kişi)</label>
              <input type="number" id="ders-kapasite" min="1" value="3"></div>
          </div>
          <button class="dm-btn" onclick="dersOturumKaydet();return false;">💾 Kaydet</button>
        </div>

        {{-- KATILIMCI YONETIMI --}}
        <div id="ders-katilimci-alani" style="display:none;">
          <div class="dm-summary">
            <div><div class="t" id="ders-ozet-tip"></div><div class="d" id="ders-ozet-detay"></div></div>
            <span id="ders-doluluk-rozet" class="dm-rozet ok"></span>
          </div>

          <div class="dm-search-wrap">
            <label style="font-size:12px;font-weight:600;color:#5f6b7a;display:block;margin-bottom:5px;">Katılımcı Ekle (isim / telefon)</label>
            <input type="text" id="ders-musteri-arama" placeholder="En az 2 harf yazın" autocomplete="off"
                   style="height:40px;border:1px solid #d7dde3;border-radius:8px;padding:0 11px;width:100%;font-size:14px;">
            <div id="ders-musteri-sonuc"></div>
          </div>

          <ul class="dm-kat-liste" id="ders-katilimci-liste"></ul>

          <div class="dm-foot">
            <button class="dm-mini" onclick="dersOturumFormAc();return false;">✎ Dersi Düzenle</button>
            <button class="dm-mini danger" onclick="dersOturumIptal();return false;">🗑 Dersi İptal Et</button>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
(function(){
  function _csrf(){ return $('meta[name="csrf-token"]').attr('content'); }
  function _sube(){ return $('input[name="sube"]').val(); }
  function _post(url, data){
    data = data || {}; data.sube = _sube();
    return $.ajax({ url:url, method:'POST', data:data, headers:{'X-CSRF-TOKEN':_csrf(),'X-Requested-With':'XMLHttpRequest','Accept':'application/json'} });
  }
  function _refreshTakvim(){ if(typeof takvimyukle==='function') takvimyukle(true,false); }

  // Projenin air-datepicker'i (tr dil objesi inline). Takvim sayfasinda zaten yuklu.
  var _dersTrLang = {
     days:['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi'],
     daysShort:['Paz','Pzt','Sal','Çar','Per','Cum','Cmt'],
     daysMin:['Pz','Pt','Sa','Ça','Pe','Cu','Ct'],
     months:['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'],
     monthsShort:['Oca','Şub','Mar','Nis','May','Haz','Tem','Ağu','Eyl','Eki','Kas','Ara'],
     today:'Bugün', clear:'Temizle', dateFormat:'yyyy-mm-dd', firstDay:1
  };
  $(function(){
     if($.fn.datepicker){
        try{ $('#ders-tarih').datepicker({ language:_dersTrLang, autoClose:true, dateFormat:'yyyy-mm-dd', position:'bottom left' }); }catch(e){}
     }
  });

  window.dersOturumYeni = function(){
    $('#ders-oturum-id').val('');
    $('#ders-tipi').val(''); $('#ders-kapasite').val(3);
    $('#ders-saat').val('09:00'); $('#ders-saat-bitis').val('10:00');
    var d=$('#takvim_tarihe_gore').val()||new Date().toISOString().slice(0,10);
    $('#ders-tarih').val(d);
    $('#ders-personel').val(''); $('#ders-hizmet').val('');
    $('#ders-modal-baslik').html('👥 Yeni Grup Dersi');
    $('#ders-form-alani').show(); $('#ders-katilimci-alani').hide();
    $('#ders-oturum-modal').modal();
  };

  window.dersOturumFormAc = function(){
    $('#ders-form-alani').show(); $('#ders-katilimci-alani').hide();
  };

  window.dersOturumAc = function(id){
    $.ajax({ url:'/isletmeyonetim/ders-oturum-getir', data:{oturum_id:id, sube:_sube()}, method:'GET',
      headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'} })
    .done(function(r){
      if(!r || r.durum!=='ok'){ swal('Hata', (r&&r.mesaj)||'Oturum yüklenemedi', 'error'); return; }
      var o=r.oturum;
      $('#ders-oturum-id').val(o.id);
      $('#ders-tipi').val(o.ders_tipi); $('#ders-kapasite').val(o.kapasite);
      $('#ders-tarih').val(o.tarih); $('#ders-saat').val(o.saat); $('#ders-saat-bitis').val(o.saat_bitis);
      $('#ders-personel').val(o.personel_id||''); $('#ders-hizmet').val(o.hizmet_id||'');
      $('#ders-ozet-tip').text((o.ders_tipi||'Grup Dersi'));
      $('#ders-ozet-detay').text(o.tarih+'  '+o.saat+'-'+o.saat_bitis+(o.personel?('  •  '+o.personel):''));
      var rozet=$('#ders-doluluk-rozet').text(o.doluluk+' / '+o.kapasite);
      rozet.removeClass('ok warn full')
           .addClass(o.doluluk>=o.kapasite?'full':(o.doluluk>0?'warn':'ok'));
      _katilimciListele(r.katilimcilar);
      $('#ders-modal-baslik').html('👥 Ders Katılımcıları');
      $('#ders-form-alani').hide(); $('#ders-katilimci-alani').show();
      $('#ders-musteri-arama').val(''); $('#ders-musteri-sonuc').hide().empty();
      $('#ders-oturum-modal').modal();
    })
    .fail(function(){ swal('Hata','Oturum yüklenemedi','error'); });
  };

  function _durumBtn(k){
    function b(d,lbl,onc){ return '<b class="'+(k.durum===d?onc:'')+'" onclick="dersKatilimciDurum('+k.id+',\''+d+'\')">'+lbl+'</b>'; }
    if(k.durum==='bekleme') return '<span style="font-size:11px;font-weight:700;color:#e67e22;">BEKLEMEDE</span>';
    return '<span class="dm-durum">'+b('rezerve','Rezerve','on-r')+b('geldi','Geldi','on-g')+b('gelmedi','Gelmedi','on-x')+'</span>';
  }
  function _katilimciListele(list){
    var $l=$('#ders-katilimci-liste').empty();
    if(!list || !list.length){ $l.append('<li style="color:#95a5a6;text-align:center;">Henüz katılımcı yok.</li>'); return; }
    list.forEach(function(k){
      var paketRozet = k.hak_dusuldu ? ' <span style="font-size:10px;background:#8e44ad;color:#fff;border-radius:4px;padding:1px 5px;">paket -1</span>' : '';
      $l.append('<li><div class="dm-kat-row">'
        +'<span><strong>'+$('<i>').text(k.ad).html()+'</strong> <small style="color:#95a5a6;">'+(k.tel||'')+'</small>'+paketRozet+'</span>'
        +'<span style="white-space:nowrap;">'+_durumBtn(k)
        +' <button class="dm-x" onclick="dersKatilimciCikar('+k.id+')" title="Çıkar">×</button>'
        +'</span></div></li>');
    });
  }

  window.dersOturumKaydet = function(){
    var id=$('#ders-oturum-id').val();
    _post('/isletmeyonetim/ders-oturum-kaydet', {
      oturum_id:id, ders_tipi:$('#ders-tipi').val(), personel_id:$('#ders-personel').val(),
      hizmet_id:$('#ders-hizmet').val(),
      tarih:$('#ders-tarih').val(), saat:$('#ders-saat').val(), saat_bitis:$('#ders-saat-bitis').val(),
      kapasite:$('#ders-kapasite').val()
    }).done(function(r){
      if(r.durum==='ok'){ _refreshTakvim(); window.dersOturumAc(r.oturum_id); }
      else swal('Hata',(r&&r.mesaj)||'Kaydedilemedi','error');
    }).fail(function(){ swal('Hata','Kaydedilemedi','error'); });
  };

  window.dersOturumIptal = function(){
    var id=$('#ders-oturum-id').val();
    swal({title:'Dersi iptal et?',text:'Bu ders takvimden kalkacak.',type:'warning',showCancelButton:true,
      confirmButtonText:'İptal Et',cancelButtonText:'Vazgeç',confirmButtonColor:'#c0392b'})
    .then(function(res){ if(res.value){ _post('/isletmeyonetim/ders-oturum-sil',{oturum_id:id})
      .done(function(){ $('#ders-oturum-modal').modal('hide'); _refreshTakvim(); }); } });
  };

  window.dersKatilimciDurum = function(kid, durum){
    _post('/isletmeyonetim/ders-katilimci-durum', {katilimci_id:kid, yeni_durum:durum})
      .done(function(r){
        if(r && r.dusum==='hak_yok')
          swal({title:'Paket hakkı yok',text:'Müşterinin bu hizmete ait kullanılabilir paket/seans hakkı bulunamadı. Katılım kaydedildi ama seans düşülmedi.',type:'warning',timer:3000,showConfirmButton:false});
        else if(r && r.dusum==='dusuldu')
          swal({title:'Seans düşüldü',text:'Paketten 1 seans düşüldü.',type:'success',timer:1400,showConfirmButton:false});
        window.dersOturumAc($('#ders-oturum-id').val());
      });
  };
  window.dersKatilimciCikar = function(kid){
    _post('/isletmeyonetim/ders-katilimci-cikar', {katilimci_id:kid})
      .done(function(){ _refreshTakvim(); window.dersOturumAc($('#ders-oturum-id').val()); });
  };

  // Musteri arama (debounce)
  var _aramaTimer=null;
  $(document).on('input','#ders-musteri-arama',function(){
    var q=$(this).val(); clearTimeout(_aramaTimer);
    if(q.length<2){ $('#ders-musteri-sonuc').hide().empty(); return; }
    _aramaTimer=setTimeout(function(){
      $.ajax({url:'/isletmeyonetim/ders-musteri-ara',data:{q:q,sube:_sube()},method:'GET',
        headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}})
      .done(function(rows){
        var $box=$('#ders-musteri-sonuc').empty();
        if(!rows.length){ $box.append('<div style="padding:8px;color:#999;">Bulunamadı</div>').show(); return; }
        rows.forEach(function(m){
          $('<a href="#" style="display:block;padding:8px 10px;border-bottom:1px solid #eee;color:#333;">')
            .html('<strong>'+$('<i>').text(m.ad).html()+'</strong> <small style="color:#999;">'+(m.tel||'')+'</small>')
            .on('click',function(e){ e.preventDefault(); _katilimciEkle(m.id); })
            .appendTo($box);
        });
        $box.show();
      });
    },250);
  });
  function _katilimciEkle(userId){
    _post('/isletmeyonetim/ders-katilimci-ekle', {oturum_id:$('#ders-oturum-id').val(), user_id:userId})
    .done(function(r){
      $('#ders-musteri-arama').val(''); $('#ders-musteri-sonuc').hide().empty();
      if(r.durum==='ok'){
        if(r.katilimci_durum==='bekleme') swal({title:'Kapasite dolu',text:'Kişi BEKLEME listesine eklendi.',type:'info',timer:1800,showConfirmButton:false});
        _refreshTakvim(); window.dersOturumAc($('#ders-oturum-id').val());
      } else swal('Hata',(r&&r.mesaj)||'Eklenemedi','warning');
    })
    .fail(function(x){ var m=(x.responseJSON&&x.responseJSON.mesaj)||'Eklenemedi'; swal('Hata',m,'warning'); });
  }
})();
</script>

@endsection
