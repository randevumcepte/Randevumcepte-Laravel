@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<div class="rc-kd-page">

   {{-- Modern Page Header --}}
   <div class="rc-kd-header">
      <div class="rc-kd-header-left">
         <div class="rc-kd-title-row">
            <div class="rc-kd-icon-bubble"><i class="fa fa-book"></i></div>
            <div>
               <h1 class="rc-kd-title">{{$sayfa_baslik}}</h1>
               <nav class="rc-kd-breadcrumb" aria-label="breadcrumb">
                  <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
                  <span class="rc-kd-sep">›</span>
                  <span class="rc-kd-active">{{$sayfa_baslik}}</span>
               </nav>
            </div>
         </div>
      </div>
      <div class="rc-kd-header-right">
         @if(\App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'rapor.kasa'))
         <a onclick="gunSonuAc()" href="#" class="rc-kd-btn rc-kd-btn-info yenieklebuton330"><i class="fa fa-calendar-check-o"></i><span>Gün Sonu</span></a>
         @endif
         @yetki('finans.masraf_ekle')
         <a onclick="masrafModalAc('masraf')" href="#" class="rc-kd-btn rc-kd-btn-danger yenieklebuton331"><i class="fa fa-plus"></i><span>Yeni Masraf</span></a>
         @endyetki
         @yetki('finans.kasa_giris_cikis')
         <a href="#" data-toggle="modal" data-target="#kasaya_para_koy" class="rc-kd-btn rc-kd-btn-primary yenieklebuton332"><i class="fa fa-arrow-down"></i><span>Para Ekle</span></a>
         @endyetki
         @yetki('finans.kasa_giris_cikis')
         <a href="#" data-toggle="modal" data-target="#kasadan_para_al" class="rc-kd-btn rc-kd-btn-success yenieklebuton333"><i class="fa fa-arrow-up"></i><span>Para Çek</span></a>
         @endyetki
      </div>
   </div>

@if(\App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'rapor.kasa'))

   {{-- Modern Filtre Kartı --}}
   <div class="rc-kd-filter-card">
      <div class="rc-kd-filter-head"><i class="fa fa-filter"></i><span>Filtrele</span></div>
      <div class="rc-kd-filter-grid">
         <div class="rc-kd-field">
            <label for="odeme_yontemine_gore_filtre">Ödeme Yöntemi</label>
            <select class="form-control rc-kd-select" id="odeme_yontemine_gore_filtre">
               <option value="">Hepsi</option>
               <option value="1">Nakit</option>
               <option value="2">Kredi Kartı</option>
               <option value="3">Havale / EFT</option>
               <option value="4">Online Ödeme</option>
               <option value="5">Senet</option>
            </select>
         </div>
         <div class="rc-kd-field">
            <label for="bankaya_gore_filtre">Banka</label>
            <select class="form-control rc-kd-select" id="bankaya_gore_filtre">
               <option value="">Hepsi</option>
               @foreach(\App\SatisOrtakligiModel\Bankalar::all() as $banka)
               <option value="{{$banka->id}}">{{$banka->banka}}</option>
               @endforeach
            </select>
         </div>
         <div class="rc-kd-field">
            <label for="zamana_gore_filtre_kasa">Zaman</label>
            <select class="form-control rc-kd-select" id="zamana_gore_filtre_kasa">
               <option value="{{date('Y-m-d')}} / {{date('Y-m-d')}}">Bugün</option>
               <option value="{{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}} / {{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}}">Dün</option>
               <option selected value="<?php  echo date('Y-m-01') . " / ". date('Y-m-t'); ?>">Bu ay</option>
               <option value="<?php  echo date('Y-m-01',strtotime('-1 months')) . " / ". date('Y-m-t',strtotime('-1 months')); ?>">Geçen ay</option>
               <option value="<?php echo date('Y-01-01') . " / ". date('Y-12-31'); ?>">Bu yıl</option>
               <option value="ozel">Özel</option>
            </select>
         </div>
         <div class="rc-kd-field">
            <label>&nbsp;</label>
            <button class="rc-kd-btn rc-kd-btn-warning rc-kd-btn-full" id="aylik_kasa_ozeti_buton" type="button">
               <i class="fa fa-calendar"></i><span>Devreden Aylar</span>
            </button>
         </div>
         <div class="rc-kd-field" id="kasa_baslangic" style="display:none">
            <label>Başlangıç tarihi</label>
            <input class="form-control rc-kd-input" type="text" id="kasa_baslangic_tarihi" autocomplete="off" />
         </div>
         <div class="rc-kd-field" id="kasa_bitis" style="display:none">
            <label>Bitiş tarihi</label>
            <input class="form-control rc-kd-input" type="text" id="kasa_bitis_tarihi" autocomplete="off" />
         </div>
      </div>
   </div>

   {{-- Modern Özet Kartları --}}
   <div class="rc-kd-stats">
      <div class="rc-kd-stat rc-kd-stat-gelir">
         <div class="rc-kd-stat-icon"><i class="fa fa-arrow-up"></i></div>
         <div class="rc-kd-stat-data">
            <div class="rc-kd-stat-value"><span id="kasa_gelir_tutari">{{$kasa['gelir']}}</span> ₺</div>
            <div class="rc-kd-stat-label">Gelir</div>
         </div>
      </div>
      <div class="rc-kd-stat rc-kd-stat-gider">
         <div class="rc-kd-stat-icon"><i class="fa fa-arrow-down"></i></div>
         <div class="rc-kd-stat-data">
            <div class="rc-kd-stat-value"><span id="kasa_gider_tutari">{{$kasa['gider']}}</span> ₺</div>
            <div class="rc-kd-stat-label">Gider</div>
         </div>
      </div>
      <div class="rc-kd-stat rc-kd-stat-net">
         <div class="rc-kd-stat-icon"><i class="fa fa-money"></i></div>
         <div class="rc-kd-stat-data">
            <div class="rc-kd-stat-value"><span id="kasa_toplam_tutar">{{$kasa['toplam']}}</span> ₺</div>
            <div class="rc-kd-stat-label">
               <span id="kasa_period_label">Dönem Net Karı</span>
               <small id="kasa_date_range" style="display:none;font-size:10px;"></small>
            </div>
         </div>
      </div>
      <div class="rc-kd-stat rc-kd-stat-ciro">
         <div class="rc-kd-stat-icon"><i class="fa fa-line-chart"></i></div>
         <div class="rc-kd-stat-data">
            <div class="rc-kd-stat-value"><span id="toplam_ciro_tutari">{{$kasa['toplam_ciro']}}</span> ₺</div>
            <div class="rc-kd-stat-label">Toplam Kazanç</div>
         </div>
      </div>
   </div>

   {{-- Gelirler & Giderler Tabloları --}}
   <div class="rc-kd-tables">
      <div class="rc-kd-table-card">
         <div class="rc-kd-table-head rc-kd-table-head-gelir">
            <i class="fa fa-arrow-up"></i><h2>Gelirler</h2>
         </div>
         <div class="rc-kd-table-wrap">
            <table class="table rc-kd-table rc-kd-gelir">
               <thead>
                  <tr>
                     <th>Tarih</th>
                     <th>Müşteri</th>
                     <th>Tahsil Eden</th>
                     <th>Notlar</th>
                     <th>Ödeme Yöntemi &amp; Banka</th>
                     <th>Tutar (₺)</th>
                  </tr>
               </thead>
               <tbody id="tahsilatlar_listesi">
                  {!! $kasa['tahsilatlar'] !!}
               </tbody>
            </table>
         </div>
      </div>
      @yetki('finans.masraf_gor')
      <div class="rc-kd-table-card">
         <div class="rc-kd-table-head rc-kd-table-head-gider">
            <i class="fa fa-arrow-down"></i><h2>Giderler</h2>
         </div>
         <div class="rc-kd-table-wrap">
            <table class="table rc-kd-table rc-kd-gider">
               <thead>
                  <tr>
                     <th>Tarih</th>
                     <th>Harcayan</th>
                     <th>Açıklama</th>
                     <th>Ödeme Yöntemi</th>
                     <th>Tutar (₺)</th>
                     <th class="rc-kd-col-actions"></th>
                  </tr>
               </thead>
               <tbody id="masraflar_listesi">
                  {!! $kasa['masraflar'] !!}
               </tbody>
            </table>
         </div>
      </div>
      @endyetki
   </div>

@else
   <div class="rc-kd-noaccess">
      <i class="fa fa-lock"></i>
      <h3>Kasa özetine erişim yetkiniz bulunmamaktadır</h3>
   </div>
@endif
</div>

<style>
/* =================================================================
   KASA DEFTERİ — MODERN RESPONSIVE
   Markaya uygun mor (#5C008E / #9D5DC8 / #d946ef)
   ================================================================= */
.rc-kd-page {
   --rc-purple-dark: #5C008E;
   --rc-purple: #9D5DC8;
   --rc-purple-light: #f5eefe;
   --rc-purple-soft: #ead4ff;
   --rc-success: #16a34a;
   --rc-warning: #f59e0b;
   --rc-danger: #dc2626;
   --rc-info: #2563eb;
   --rc-teal: #0ea5b7;
   --rc-text: #1f2937;
   --rc-text-soft: #6b7280;
   --rc-border: #eef0f4;
}

/* === HEADER === */
.rc-kd-header {
   display: flex; align-items: center; justify-content: space-between;
   gap: 16px; flex-wrap: wrap;
   padding: 18px 22px; margin-bottom: 18px;
   background: #fff; border-radius: 14px;
   box-shadow: 0 1px 3px rgba(17,24,39,.04), 0 4px 16px rgba(92,0,142,.04);
}
.rc-kd-header-left, .rc-kd-header-right { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.rc-kd-title-row { display: flex; align-items: center; gap: 14px; }
.rc-kd-icon-bubble {
   width: 46px; height: 46px; border-radius: 12px;
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%);
   color: #fff; display: inline-flex; align-items: center; justify-content: center;
   font-size: 18px; box-shadow: 0 6px 18px rgba(92,0,142,.25); flex-shrink: 0;
}
.rc-kd-title { margin: 0; font-size: 19px; font-weight: 700; color: var(--rc-text); line-height: 1.2; }
.rc-kd-breadcrumb { margin-top: 4px; font-size: 12.5px; color: var(--rc-text-soft); display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.rc-kd-breadcrumb a { color: var(--rc-text-soft); text-decoration: none; transition: color .15s; }
.rc-kd-breadcrumb a:hover { color: var(--rc-purple-dark); }
.rc-kd-breadcrumb .rc-kd-sep { color: #cbd5e1; }
.rc-kd-breadcrumb .rc-kd-active { color: var(--rc-purple-dark); font-weight: 600; }

/* === BUTONLAR === */
.rc-kd-btn {
   display: inline-flex; align-items: center; justify-content: center; gap: 8px;
   height: 42px; padding: 0 16px; border-radius: 999px;
   font-size: 13px; font-weight: 600; color: #fff !important;
   text-decoration: none !important; border: none; white-space: nowrap; cursor: pointer;
   transition: transform .15s, box-shadow .15s, filter .15s;
}
.rc-kd-btn i { font-size: 14px; }
.rc-kd-btn:hover { transform: translateY(-1px); filter: brightness(1.06); color: #fff; }
.rc-kd-btn:active { transform: translateY(0); }
.rc-kd-btn-full { width: 100%; height: 42px; }
.rc-kd-btn-danger  { background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); box-shadow: 0 6px 16px rgba(220,38,38,.26); }
.rc-kd-btn-primary { background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%); box-shadow: 0 6px 16px rgba(92,0,142,.28); }
.rc-kd-btn-success { background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%); box-shadow: 0 6px 16px rgba(22,163,74,.28); }
.rc-kd-btn-warning { background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); box-shadow: 0 6px 16px rgba(245,158,11,.26); }
.rc-kd-btn-info    { background: linear-gradient(135deg, #0ea5b7 0%, #22c1d6 100%); box-shadow: 0 6px 16px rgba(14,165,183,.28); }

/* === FİLTRE KARTI === */
.rc-kd-filter-card {
   background: #fff; border-radius: 14px; padding: 18px 20px 20px; margin-bottom: 18px;
   box-shadow: 0 1px 3px rgba(17,24,39,.04), 0 4px 16px rgba(92,0,142,.04);
}
.rc-kd-filter-head {
   display: inline-flex; align-items: center; gap: 8px;
   font-size: 12.5px; font-weight: 700; color: var(--rc-purple-dark);
   text-transform: uppercase; letter-spacing: .04em;
   padding-bottom: 14px; margin-bottom: 14px; border-bottom: 1px solid var(--rc-border); width: 100%;
}
.rc-kd-filter-head i {
   width: 28px; height: 28px; border-radius: 8px; background: var(--rc-purple-light);
   display: inline-flex; align-items: center; justify-content: center; font-size: 13px;
}
.rc-kd-filter-grid { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 14px; align-items: end; }
.rc-kd-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.rc-kd-field label {
   font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
   color: var(--rc-text-soft); margin: 0; min-height: 14px;
}
.rc-kd-page .rc-kd-select,
.rc-kd-page .rc-kd-input {
   height: 42px !important; border: 1px solid var(--rc-border) !important; border-radius: 10px !important;
   padding: 0 14px !important; font-size: 13.5px !important; color: var(--rc-text) !important;
   background-color: #fafbfc !important; width: 100%;
   transition: border-color .15s, box-shadow .15s, background-color .15s;
   -webkit-appearance: none; -moz-appearance: none; appearance: none;
   background-repeat: no-repeat; background-position: right 12px center; background-size: 12px;
}
.rc-kd-page .rc-kd-select {
   background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
   padding-right: 36px !important;
}
.rc-kd-page .rc-kd-select:focus,
.rc-kd-page .rc-kd-input:focus {
   border-color: var(--rc-purple) !important; background-color: #fff !important;
   box-shadow: 0 0 0 4px rgba(157,93,200,.12) !important; outline: none;
}

/* === ÖZET KARTLARI === */
.rc-kd-stats { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 16px; margin-bottom: 18px; }
.rc-kd-stat {
   display: flex; align-items: center; gap: 14px;
   background: #fff; border: 1px solid var(--rc-border); border-radius: 16px;
   padding: 18px 20px; box-shadow: 0 1px 3px rgba(17,24,39,.04), 0 6px 20px rgba(92,0,142,.05);
   position: relative; overflow: hidden;
}
.rc-kd-stat::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; }
.rc-kd-stat-gelir::before { background: linear-gradient(180deg, #0ea5b7, #46cbda); }
.rc-kd-stat-gider::before { background: linear-gradient(180deg, var(--rc-purple-dark), var(--rc-purple)); }
.rc-kd-stat-net::before   { background: linear-gradient(180deg, #1d4ed8, #3b82f6); }
.rc-kd-stat-ciro::before  { background: linear-gradient(180deg, #15803d, #22c55e); }
.rc-kd-stat-icon {
   width: 48px; height: 48px; border-radius: 12px; flex-shrink: 0;
   display: inline-flex; align-items: center; justify-content: center; font-size: 18px; color: #fff;
}
.rc-kd-stat-gelir .rc-kd-stat-icon { background: linear-gradient(135deg, #0ea5b7, #46cbda); box-shadow: 0 6px 16px rgba(14,165,183,.30); }
.rc-kd-stat-gider .rc-kd-stat-icon { background: linear-gradient(135deg, var(--rc-purple-dark), var(--rc-purple)); box-shadow: 0 6px 16px rgba(92,0,142,.28); }
.rc-kd-stat-net .rc-kd-stat-icon   { background: linear-gradient(135deg, #1d4ed8, #3b82f6); box-shadow: 0 6px 16px rgba(29,78,216,.28); }
.rc-kd-stat-ciro .rc-kd-stat-icon  { background: linear-gradient(135deg, #15803d, #22c55e); box-shadow: 0 6px 16px rgba(21,128,61,.28); }
.rc-kd-stat-data { min-width: 0; }
.rc-kd-stat-value { font-size: 22px; font-weight: 800; color: var(--rc-text); line-height: 1.1; letter-spacing: -.5px; word-break: break-word; }
.rc-kd-stat-label { font-size: 12.5px; font-weight: 600; color: var(--rc-text-soft); margin-top: 3px; }
.rc-kd-stat-label small { display: block; color: var(--rc-text-soft); }

/* === TABLO KARTLARI === */
/* Masaustu: Gelirler & Giderler yan yana. Tablolar min-width tasimadigi ve
   basliklar sarabildigi icin yarim kaba sigar, Tutar sutunu kesilmez.
   <=1100px tek sutun (tablet), <=900px kart gorunumu. */
.rc-kd-tables { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 30px; }
.rc-kd-table-card {
   background: #fff; border: 1px solid var(--rc-border); border-radius: 16px;
   box-shadow: 0 1px 3px rgba(17,24,39,.04), 0 6px 24px rgba(92,0,142,.05); overflow: hidden;
}
.rc-kd-table-head {
   display: flex; align-items: center; gap: 10px; padding: 16px 20px;
   border-bottom: 1px solid var(--rc-border);
}
.rc-kd-table-head h2 { margin: 0; font-size: 15px; font-weight: 700; color: var(--rc-text); }
.rc-kd-table-head i { width: 30px; height: 30px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; color: #fff; }
.rc-kd-table-head-gelir i { background: linear-gradient(135deg, #0ea5b7, #46cbda); }
.rc-kd-table-head-gider i { background: linear-gradient(135deg, var(--rc-purple-dark), var(--rc-purple)); }
.rc-kd-table-wrap { width: 100%; overflow-x: auto; }

/* === TABLO === */
/* min-width YOK: tablo kabina sigar (yarim genislikte bile tasmaz). */
.rc-kd-table { width: 100% !important; margin: 0 !important; border-collapse: separate !important; border-spacing: 0 !important; min-width: 0; }
.rc-kd-table thead th {
   background: var(--rc-purple-light) !important; color: var(--rc-purple-dark) !important;
   font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .02em;
   padding: 8px 7px !important; text-align: left; border: none !important;
   border-bottom: 2px solid var(--rc-purple-soft) !important; white-space: normal; vertical-align: middle;
}
.rc-kd-table tbody td {
   padding: 6px 7px !important; font-size: 12.5px; color: var(--rc-text);
   border: none !important; border-bottom: 1px solid var(--rc-border) !important;
   vertical-align: middle !important; line-height: 1.3; word-wrap: break-word; overflow-wrap: break-word;
}
.rc-kd-table tbody tr:last-child td { border-bottom: none !important; }
.rc-kd-table tbody tr:nth-child(even) { background: #fcfbfe; }
.rc-kd-table tbody tr:hover { background: #f7f1fd; }
/* Tiklanabilir gelir satiri */
.rc-kd-gelir-row { cursor: pointer; }
.rc-kd-gelir-row:hover { background: #f1ebfb !important; }
/* Tutar sütunu (gelir 6, gider 5) sağa yasli + kalın */
.rc-kd-gelir tbody td:nth-of-type(6),
.rc-kd-gider tbody td:nth-of-type(5) { font-weight: 700; color: var(--rc-text); white-space: nowrap; }
/* Tarih sütunu tek satirda kalsin (cirkin kirilmayi onle) */
.rc-kd-gelir tbody td:nth-of-type(1),
.rc-kd-gider tbody td:nth-of-type(1) { white-space: nowrap; }
/* Aksiyon hucresi: sil/duzenle butonlari yan yana kalsin (satir kisalir) */
.rc-kd-table tbody td:last-child { white-space: nowrap; }
.rc-kd-table thead th:last-child.rc-kd-col-actions { text-align: right; }

/* Satır içi sil/düzenle butonları → kompakt yuvarlak */
.rc-kd-table tbody td .btn.btn-danger {
   width: 26px; height: 26px; padding: 0 !important; border-radius: 50% !important;
   display: inline-flex !important; align-items: center; justify-content: center;
   background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%) !important; border: none !important;
   color: #fff !important; font-size: 11px !important; line-height: 1 !important;
   box-shadow: 0 2px 6px rgba(220,38,38,.25); transition: transform .12s, filter .12s;
}
.rc-kd-table tbody td .btn.btn-danger:hover { transform: scale(1.08); filter: brightness(1.06); }
.rc-kd-table tbody td .btn.btn-primary {
   width: 26px; height: 26px; padding: 0 !important; border-radius: 50% !important;
   display: inline-flex !important; align-items: center; justify-content: center;
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%) !important; border: none !important;
   color: #fff !important; font-size: 11px !important; margin-right: 3px;
}
.rc-kd-table tbody td:empty { padding: 6px 7px !important; }

/* === ERİŞİM YOK === */
.rc-kd-noaccess {
   background: #fff; border: 1px solid var(--rc-border); border-radius: 16px;
   padding: 50px 20px; text-align: center;
   box-shadow: 0 1px 3px rgba(17,24,39,.04), 0 6px 24px rgba(92,0,142,.05);
}
.rc-kd-noaccess i { font-size: 42px; color: var(--rc-purple); margin-bottom: 14px; display: block; }
.rc-kd-noaccess h3 { color: var(--rc-text); font-size: 17px; margin: 0; }

/* === RESPONSIVE: TABLET LANDSCAPE (≤1100px) === */
@media (max-width: 1100px) {
   .rc-kd-stats { grid-template-columns: repeat(2, minmax(0,1fr)); }
}
/* Tablolar olabildigince yan yana kalsin; ancak <=820px'te tek sutuna gec
   (6 sutun yarim kaba sigamaz hale gelince). */
@media (max-width: 820px) {
   .rc-kd-tables { grid-template-columns: 1fr; }
}

/* === RESPONSIVE: TABLET (≤1024px) === */
@media (max-width: 1024px) {
   .rc-kd-header { padding: 14px 16px; }
   .rc-kd-icon-bubble { width: 40px; height: 40px; font-size: 16px; }
   .rc-kd-title { font-size: 17px; }
   .rc-kd-btn { height: 38px; padding: 0 13px; font-size: 12.5px; }
   .rc-kd-filter-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }
   .rc-kd-stat-value { font-size: 20px; }
}

/* === RESPONSIVE: TABLET PORTRAIT + MOBILE (≤720px) === */
@media (max-width: 720px) {
   .rc-kd-header { padding: 12px 14px; border-radius: 12px; }
   .rc-kd-header-left { width: 100%; }
   .rc-kd-header-right { width: 100%; }
   .rc-kd-header-right .rc-kd-btn { flex: 1; min-width: 0; padding: 0 10px; }
   .rc-kd-title { font-size: 16px; }

   .rc-kd-filter-card { padding: 14px 14px 16px; border-radius: 12px; }
   .rc-kd-filter-grid { grid-template-columns: 1fr; gap: 12px; }
   .rc-kd-field label[for], .rc-kd-field > label { min-height: 0; }

   .rc-kd-stats { gap: 12px; }

   /* Tabloları blok-yığın yap */
   .rc-kd-table-wrap { overflow-x: visible; }
   .rc-kd-table { min-width: 0 !important; display: block !important; }
   .rc-kd-table thead { display: none !important; }
   .rc-kd-table tbody { display: block !important; }
   .rc-kd-table tbody tr {
      display: block !important; background: #fff !important;
      border: 1px solid var(--rc-border) !important; border-radius: 12px !important;
      box-shadow: 0 2px 8px rgba(92,0,142,.05); padding: 4px 2px; margin: 0 8px 12px;
   }
   .rc-kd-table tbody tr:nth-child(even) { background: #fff !important; }
   .rc-kd-table tbody td {
      display: flex !important; justify-content: space-between; align-items: center; gap: 14px;
      padding: 8px 14px !important; border: none !important;
      border-bottom: 1px solid #f4f0fa !important; text-align: right; white-space: normal !important;
   }
   .rc-kd-table tbody tr td:last-child { border-bottom: none !important; }
   .rc-kd-table tbody td::before {
      content: attr(data-label); flex: 0 0 auto; margin-right: auto;
      font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
      color: var(--rc-purple-dark); text-align: left;
   }
   /* Sunucudan gelen satirlarda data-label yok; nth-of-type ile etiketle */
   .rc-kd-gelir tbody td:nth-of-type(1)::before { content: "Tarih"; }
   .rc-kd-gelir tbody td:nth-of-type(2)::before { content: "Müşteri"; }
   .rc-kd-gelir tbody td:nth-of-type(3)::before { content: "Tahsil Eden"; }
   .rc-kd-gelir tbody td:nth-of-type(4)::before { content: "Notlar"; }
   .rc-kd-gelir tbody td:nth-of-type(5)::before { content: "Ödeme & Banka"; }
   .rc-kd-gelir tbody td:nth-of-type(6)::before { content: "Tutar (₺)"; }
   .rc-kd-gelir tbody td:nth-of-type(7)::before { content: ""; }
   .rc-kd-gider tbody td:nth-of-type(1)::before { content: "Tarih"; }
   .rc-kd-gider tbody td:nth-of-type(2)::before { content: "Harcayan"; }
   .rc-kd-gider tbody td:nth-of-type(3)::before { content: "Açıklama"; }
   .rc-kd-gider tbody td:nth-of-type(4)::before { content: "Ödeme Yöntemi"; }
   .rc-kd-gider tbody td:nth-of-type(5)::before { content: "Tutar (₺)"; }
   .rc-kd-gider tbody td:nth-of-type(6)::before { content: ""; }
   /* Tutar satırını vurgula */
   .rc-kd-gelir tbody td:nth-of-type(6),
   .rc-kd-gider tbody td:nth-of-type(5) { font-size: 14px; color: var(--rc-purple-dark); }
}

/* === RESPONSIVE: KÜÇÜK MOBILE (≤420px) === */
@media (max-width: 420px) {
   .rc-kd-header-right { flex-direction: column; }
   .rc-kd-header-right .rc-kd-btn { width: 100%; }
   .rc-kd-title-row { gap: 10px; }
   .rc-kd-icon-bubble { width: 36px; height: 36px; font-size: 14px; }
   .rc-kd-stats { grid-template-columns: 1fr; }
   .rc-kd-table tbody td { padding: 7px 12px !important; font-size: 12.5px; }
}
</style>

  <!-- yeni masraf -->
      <div
         id="kasaya_para_koy"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="max-height: 90%;">
               <form id="kasaya_para_koy_form"  method="POST">
                  <div class="modal-header">
                     <h2 >Kasaya Para Ekle</h2>
                  </div>
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <input type="hidden" name="sube" value="{{$isletme->id}}">
                     <input type="hidden" name="paraekle_id" id='paraekle_id' value="">

                     <div class="row" data-value="0">
                        <div class="col-md-6">
                           <label>Tarih</label>
                           <input type="text" required class="form-control date-picker" name="parakoyma_tarihi" id='parakoyma_tarihi' value="{{date('Y-m-d')}}" autocomplete="off">
                        </div>
                        <div class="col-md-6">
                           <label>Tutar (₺)</label>
                           <input type="tel" name="para_tutari" id='para_tutari' required class="form-control try-currency">
                        </div>
                        <div class="col-md-12">
                           <label>Açıklama</label>
                           <textarea name="para_aciklama" id='para_aciklama' class="form-control"></textarea>
                        </div>
                     </div>

                     <div class="row" data-value="0">
                        <div class="col-md-6">
                           <label>Ödeme Yöntemi</label>
                           <select name="para_odeme_yontemi" id='para_odeme_yontemi' class="form-control custom-select2" style="width: 100%;">
                              @foreach(\App\OdemeYontemleri::all() as $odeme_yontemi)
                              <option value="{{$odeme_yontemi->id}}">{{$odeme_yontemi->odeme_yontemi}}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>Para Ekleyen</label>
                           <select name="paraekleyen" id='paraekleyen' class="form-control custom-select2 personel_secimi" style="width: 100%;">
                              <option></option>
                           </select>
                        </div>
                     </div>

                     <div class="modal-footer" style="display:block">
                        <div class="row" data-value="0">
                           <div class="col-md-6  col-sm-6 col-xs-6 col-6">
                              <button type="submit" {{Auth::guard('satisortakligi')->check() ? 'disabled' : ''}} class="btn btn-success btn-lg btn-block"> <i class="fa fa-save"></i>
                              Kaydet </button>
                           </div>
                           <div class="col-md-6  col-sm-6 col-xs-6 col-6">
                              <button
                                 type="button"
                                 class="btn btn-danger btn-lg btn-block"
                                 data-dismiss="modal"
                                 > <i class="fa fa-times"></i>
                              Kapat
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
          <div
         id="kasadan_para_al"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="max-height: 90%;">
               <form id="kasadan_para_al_form"  method="POST">
                  <div class="modal-header">
                     <h2 >Kasadan Para Çek</h2>
                  </div>
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <input type="hidden" name="sube" value="{{$isletme->id}}">
                     <input type="hidden" name="paraalid" id='paraalid' value="">

                     <div class="row" data-value="0">
                        <div class="col-md-6">
                           <label>Tarih</label>
                           <input type="text" required class="form-control date-picker" name="paraalma_tarihi" id='paraalma_tarihi' value="{{date('Y-m-d')}}" autocomplete="off">
                        </div>
                        <div class="col-md-6">
                           <label>Tutar (₺)</label>
                           <input type="tel" name="paraalma_tutari" id='paraalma_tutari' required class="form-control try-currency">
                        </div>
                        <div class="col-md-6">
                           <label>Açıklama</label>
                           <textarea name="paraalma_aciklama" id='paraalma_aciklama' class="form-control"></textarea>
                        </div>
                          <div class="col-md-3">
                           <label>Onay Kodu</label>
                           <input type="tel" name="onaykoduparacekme" id='onaykoduparacekme' required class="form-control">
                        </div>
                        <div class="col-md-3">
                           <label style="color:white;">Onay Kodu</label>
                           <button {{Auth::guard('satisortakligi')->check() ? 'disabled' : ''}} class="btn-block btn btn-lg btn-primary" type="button" id="paracekmeonaykodu" name="paracekmeonaykodu">Kod Gönder</button>
                        </div>
                     </div>

                     <div class="row" data-value="0">
                        <div class="col-md-6">
                           <label>Ödeme Yöntemi</label>
                           <select name="paraalma_odeme_yontemi" id='paraalma_odeme_yontemi' class="form-control custom-select2" style="width: 100%;">
                              @foreach(\App\OdemeYontemleri::all() as $odeme_yontemi)
                              <option value="{{$odeme_yontemi->id}}">{{$odeme_yontemi->odeme_yontemi}}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>Para Çeken</label>
                           <select name="paraalan" id='paraalan' class="form-control custom-select2 personel_secimi" style="width: 100%;">
                              <option></option>
                           </select>
                        </div>
                     </div>

                     <div class="modal-footer" style="display:block">
                        <div class="row" data-value="0">
                           <div class="col-md-6  col-sm-6 col-xs-6 col-6">
                              <button type="submit" {{Auth::guard('satisortakligi')->check() ? 'disabled' : ''}} class="btn btn-success btn-lg btn-block"> <i class="fa fa-save"></i>
                              Kaydet </button>
                           </div>
                           <div class="col-md-6  col-sm-6 col-xs-6 col-6">
                              <button
                                 type="button"
                                 class="btn btn-danger btn-lg btn-block"
                                 data-dismiss="modal"
                                 > <i class="fa fa-times"></i>
                              Kapat
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
      <!-- Devreden Aylar Modal -->
<div id="devreden_aylar_modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Devreden Aylar</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Yıl Seçin</label>
                        <select id="devreden_aylar_yil" class="form-control">
                            @php
                                $currentYear = date('Y');
                                $startYear = 2015; // İşletmenizin başlangıç yılı
                            @endphp
                            @for($year = $currentYear; $year >= $startYear; $year--)
                                <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                    </div>

                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Ay</th>
                                <th>Toplam Kasa (₺)</th>
                                 <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody id="devreden_aylar_listesi">
                            <!-- AJAX ile dolacak -->
                        </tbody>

                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- Gelir (Tahsilat) Detay Popup -->
<div id="rc-gd-overlay" class="rc-gd-overlay">
   <div class="rc-gd-modal" role="dialog" aria-modal="true">
      <div class="rc-gd-head">
         <div class="rc-gd-head-left">
            <span class="rc-gd-badge" id="rc-gd-tip">Tahsilat</span>
            <h3 class="rc-gd-title">Gelir Detayı</h3>
         </div>
         <button type="button" class="rc-gd-close" id="rc-gd-close" aria-label="Kapat">&times;</button>
      </div>
      <div class="rc-gd-body" id="rc-gd-body">
         <!-- JS ile dolacak -->
      </div>
   </div>
</div>

<style>
.rc-gd-overlay {
   display: none; position: fixed; inset: 0; z-index: 99999;
   background: rgba(15, 23, 42, .55); backdrop-filter: blur(2px);
   align-items: center; justify-content: center; padding: 20px;
}
.rc-gd-overlay.show { display: flex; }
.rc-gd-modal {
   background: #fff; width: 100%; max-width: 560px; max-height: 88vh; overflow: hidden;
   border-radius: 18px; box-shadow: 0 24px 60px rgba(15,23,42,.35);
   display: flex; flex-direction: column;
   animation: rcgdIn .18s ease-out;
}
@keyframes rcgdIn { from { opacity: 0; transform: translateY(14px) scale(.98); } to { opacity: 1; transform: none; } }
.rc-gd-head {
   display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
   padding: 18px 22px; border-bottom: 1px solid #eef0f4;
   background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
}
.rc-gd-head-left { display: flex; flex-direction: column; gap: 8px; }
.rc-gd-badge {
   display: inline-block; align-self: flex-start; font-size: 11px; font-weight: 700;
   text-transform: uppercase; letter-spacing: .04em; color: #c7d2fe;
   background: rgba(99,102,241,.18); border: 1px solid rgba(199,210,254,.3);
   padding: 3px 10px; border-radius: 999px;
}
.rc-gd-title { margin: 0; font-size: 17px; font-weight: 700; color: #fff; }
.rc-gd-close {
   background: rgba(255,255,255,.12); border: none; color: #fff; width: 32px; height: 32px;
   border-radius: 9px; font-size: 20px; line-height: 1; cursor: pointer; flex-shrink: 0;
   transition: background .15s;
}
.rc-gd-close:hover { background: rgba(255,255,255,.25); }
.rc-gd-body { padding: 18px 22px 22px; overflow-y: auto; }

/* Tutar vurgu kutusu */
.rc-gd-amount {
   text-align: center; padding: 16px; margin-bottom: 18px;
   background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px;
}
.rc-gd-amount .lbl { font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
.rc-gd-amount .val { font-size: 28px; font-weight: 800; color: #4338ca; letter-spacing: -.5px; margin-top: 2px; }

/* Bilgi gridi */
.rc-gd-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 16px; margin-bottom: 6px; }
.rc-gd-item { min-width: 0; }
.rc-gd-item .k { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #94a3b8; margin-bottom: 2px; }
.rc-gd-item .v { font-size: 14px; font-weight: 600; color: #1e293b; word-break: break-word; }
.rc-gd-item.full { grid-column: 1 / -1; }

/* Kalemler tablosu */
.rc-gd-section-title {
   font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
   color: #475569; margin: 18px 0 10px; padding-top: 16px; border-top: 1px dashed #e2e8f0;
}
.rc-gd-kalemler { display: flex; flex-direction: column; gap: 8px; }
.rc-gd-kalem {
   display: flex; align-items: center; gap: 12px; padding: 10px 12px;
   background: #f8fafc; border: 1px solid #eef0f4; border-radius: 10px;
}
.rc-gd-kalem .ktip {
   font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em;
   color: #4f46e5; background: #eef2ff; border: 1px solid #e0e7ff;
   padding: 2px 8px; border-radius: 6px; flex-shrink: 0; min-width: 56px; text-align: center;
}
.rc-gd-kalem .kad { flex: 1; min-width: 0; font-size: 13.5px; font-weight: 600; color: #1e293b; }
.rc-gd-kalem .kad small { display: block; font-size: 11.5px; font-weight: 500; color: #94a3b8; margin-top: 1px; }
.rc-gd-kalem .ktutar { font-size: 13.5px; font-weight: 700; color: #1e293b; white-space: nowrap; }
.rc-gd-empty { text-align: center; color: #94a3b8; font-size: 13px; padding: 8px 0; }
.rc-gd-loading { text-align: center; color: #64748b; padding: 30px 0; font-size: 14px; }

@media (max-width: 480px) {
   .rc-gd-grid { grid-template-columns: 1fr; }
   .rc-gd-amount .val { font-size: 24px; }
}
</style>

<script>
(function(){
   var $overlay = $('#rc-gd-overlay');
   // appendTo body — parent stacking context'ten kac, tam viewport ortasi
   $overlay.appendTo('body');

   function esc(s){ return $('<div>').text(s == null ? '' : s).html(); }

   function closeModal(){ $overlay.removeClass('show'); }
   $('#rc-gd-close').on('click', closeModal);
   $overlay.on('click', function(e){ if(e.target === this) closeModal(); });
   $(document).on('keydown', function(e){ if(e.key === 'Escape') closeModal(); });

   // Gelir satirina tiklama (liste AJAX ile yenilendigi icin delegation).
   // Class yerine data-tahsilat-id olan herhangi bir satiri yakala (daha dayanikli).
   $(document).on('click', '#tahsilatlar_listesi tr[data-tahsilat-id]', function(e){
      // Satir icindeki butonlara (sil vb.) tiklaninca popup acma
      if($(e.target).closest('button, a').length) return;

      var id = $(this).attr('data-tahsilat-id');
      if(!id) return;

      $('#rc-gd-tip').text('Tahsilat');
      $('#rc-gd-body').html('<div class="rc-gd-loading"><i class="fa fa-spinner fa-spin"></i> Yükleniyor...</div>');
      $overlay.addClass('show');

      $.ajax({
         url: '/isletmeyonetim/kasagelirdetay',
         type: 'GET',
         dataType: 'json',
         data: { tahsilat_id: id, sube: $('input[name="sube"]').val() },
         success: function(d){
            if(!d || d.durum !== 'ok'){
               $('#rc-gd-body').html('<div class="rc-gd-empty">' + esc((d && d.mesaj) || 'Detay yüklenemedi.') + '</div>');
               return;
            }
            $('#rc-gd-tip').text(d.tip);

            var html = '';
            html += '<div class="rc-gd-amount"><div class="lbl">Tahsil Edilen Tutar</div><div class="val">' + esc(d.tutar) + ' ₺</div></div>';

            html += '<div class="rc-gd-grid">';
            html += '<div class="rc-gd-item"><div class="k">Tarih</div><div class="v">' + esc(d.tarih) + (d.saat && d.saat !== '00:00' ? ' <small style="color:#94a3b8;font-weight:500">' + esc(d.saat) + '</small>' : '') + '</div></div>';
            html += '<div class="rc-gd-item"><div class="k">Müşteri</div><div class="v">' + esc(d.musteri) + '</div></div>';
            html += '<div class="rc-gd-item"><div class="k">Tahsil Eden</div><div class="v">' + esc(d.tahsil_eden) + '</div></div>';
            html += '<div class="rc-gd-item"><div class="k">Ödeme Yöntemi</div><div class="v">' + esc(d.odeme_yontemi) + (d.banka ? ' <small style="color:#94a3b8;font-weight:500">' + esc(d.banka) + '</small>' : '') + '</div></div>';
            if(d.satici){
               html += '<div class="rc-gd-item"><div class="k">Satış Personeli</div><div class="v">' + esc(d.satici) + '</div></div>';
            }
            if(d.adisyon_id){
               html += '<div class="rc-gd-item"><div class="k">Adisyon No</div><div class="v">#' + esc(d.adisyon_id) + (d.harici ? ' <small style="color:#f59e0b;font-weight:600">Harici</small>' : '') + '</div></div>';
            }
            if(d.notlar){
               html += '<div class="rc-gd-item full"><div class="k">Notlar</div><div class="v">' + esc(d.notlar) + '</div></div>';
            }
            html += '</div>';

            // Kalemler
            if(d.kalemler && d.kalemler.length){
               html += '<div class="rc-gd-section-title">Ödenen Kalemler</div>';
               html += '<div class="rc-gd-kalemler">';
               d.kalemler.forEach(function(k){
                  var ad = esc(k.ad) + (k.adet && k.adet > 1 ? ' <small>x' + esc(k.adet) + '</small>' : '');
                  if(k.personel) ad += '<small>' + esc(k.personel) + '</small>';
                  html += '<div class="rc-gd-kalem">' +
                          '<span class="ktip">' + esc(k.tip) + '</span>' +
                          '<span class="kad">' + ad + '</span>' +
                          '<span class="ktutar">' + esc(k.tutar) + ' ₺</span>' +
                          '</div>';
               });
               html += '</div>';
            } else if(d.adisyon_id){
               html += '<div class="rc-gd-section-title">Ödenen Kalemler</div><div class="rc-gd-empty">Kalem bilgisi bulunamadı.</div>';
            }

            $('#rc-gd-body').html(html);
         },
         error: function(){
            $('#rc-gd-body').html('<div class="rc-gd-empty">Detay yüklenirken bir hata oluştu.</div>');
         }
      });
   });

   // Gider (masraf) DÜZENLE — kasa sayfasi listesi icin.
   // custom.js'deki handler #masraf_tablo'ya bagli (bu sayfada yok), o yuzden burada delegation ile.
   // Modal data-toggle ile acilir; biz sadece alanlari doldururuz.
   $(document).on('click', '#masraflar_listesi button[name="masraf_duzenle"]', function(){
      var masrafid = $(this).attr('data-value');
      if(!masrafid) return;
      $.ajax({
         type: 'GET',
         url: '/isletmeyonetim/masraf-detay',
         dataType: 'json',
         data: { sube: $('input[name="sube"]').val(), masraf_id: masrafid },
         beforeSend: function(){ $('#preloader').show(); },
         success: function(result){
            $('#preloader').hide();
            // Tutar Turkce formatta olmali (kayit str_replace('.'->'' , ','->'.') yapiyor)
            var t = parseFloat(result.tutar);
            var tutarTr = isNaN(t) ? (result.tutar || '') : t.toLocaleString('tr-TR', {minimumFractionDigits:2, maximumFractionDigits:2});
            $('#masraf_tutari').val(tutarTr);
            $('#masraf_tarihi').val(result.tarih);
            $('#masraf_aciklama').val(result.aciklama || '');
            $('#masraf_kategorisi').val(result.masraf_kategori_id).trigger('change');
            $('#masraf_odeme_yontemi').val(result.odeme_yontemi_id).trigger('change');
            // harcayan select2 AJAX tabanli — option DOM'da yoksa olustur, sonra sec
            var $harcayan = $('#harcayan');
            if(result.harcayan_id){
               if($harcayan.find('option[value="'+result.harcayan_id+'"]').length === 0){
                  $harcayan.append(new Option(result.harcayan_adi || ('#'+result.harcayan_id), result.harcayan_id, true, true));
               }
               $harcayan.val(result.harcayan_id).trigger('change');
            } else {
               $harcayan.val('').trigger('change');
            }
            $('#masraf_id').val(result.id);
            // Kayit tipine gore modu ayarla (masraf / personel gideri) — birlesik modal
            if(typeof masrafModalMod === 'function'){
               masrafModalMod(parseInt(result.personel_gideri)===1 ? 'gideri' : 'masraf');
            }
         },
         error: function(request){
            $('#preloader').hide();
            if(document.getElementById('hata')) document.getElementById('hata').innerHTML = request.responseText;
         }
      });
   });
})();
</script>

      <script>
         // Devreden Aylar butonuna tıklama
$('#aylik_kasa_ozeti_buton').click(function() {
    $('#devreden_aylar_modal').modal('show');
    getDevredenAylar($('#devreden_aylar_yil').val());
});

// Yıl değiştiğinde
$('#devreden_aylar_yil').change(function() {
    getDevredenAylar($(this).val());
});

// Yenile butonu
$('#devreden_aylar_getir').click(function() {
    getDevredenAylar($('#devreden_aylar_yil').val());
});

// Devreden ayları getiren fonksiyon
function getDevredenAylar(yil) {
    $('#devreden_aylar_listesi').html('<tr><td colspan="3" class="text-center"><i class="fa fa-spinner fa-spin"></i> Yükleniyor...</td></tr>');

    $.ajax({
        url: '/isletmeyonetim/devreden-aylar', // Route tanımlamanız gerekecek
        type: 'GET',
        data: {
            yil: yil,
            sube: '{{ $isletme->id }}',
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                renderDevredenAylar(response.data);
            } else {
                $('#devreden_aylar_listesi').html('<tr><td colspan="3" class="text-center text-danger">' + response.message + '</td></tr>');
            }
        },
        error: function() {
            $('#devreden_aylar_listesi').html('<tr><td colspan="3" class="text-center text-danger">Bir hata oluştu!</td></tr>');
        }
    });
}

// Listeyi render eden fonksiyon
function renderDevredenAylar(data) {
    var html = '';
    var yilToplam = 0;

    if (data.length === 0) {
        html = '<tr><td colspan="3" class="text-center">Kayıt bulunamadı</td></tr>';
    } else {
        data.forEach(function(ay) {
            yilToplam += parseFloat(ay.donem_net_kar);

            var durumClass = ay.donem_net_kar > 0 ? 'text-success' : (ay.donem_net_kar < 0 ? 'text-danger' : 'text-secondary');
            var durumIcon = ay.donem_net_kar > 0 ? 'fa-chevron-up' : (ay.donem_net_kar < 0 ? 'fa-chevron-down' : 'fa-minus');

            html += '<tr>' +
                    '<td><strong>' + ay.ay_adi + ' ' + ay.yil + '</strong></td>' +
                    '<td class="' + durumClass + '">' +
                    '<i class="fa ' + durumIcon + '"></i> ' +
                    formatCurrency(ay.donem_net_kar) + ' ₺' +
                    '</td>' +
                    '<td>' +
                    '<span class="badge ' + (ay.donem_net_kar > 0 ? 'badge-success' : (ay.donem_net_kar < 0 ? 'badge-danger' : 'badge-secondary')) + '">' +
                    (ay.donem_net_kar > 0 ? 'KAR' : (ay.donem_net_kar < 0 ? 'ZARAR' : 'DENGELİ')) +
                    '</span>' +
                    '</td>' +
                    '</tr>';
        });
    }

    $('#devreden_aylar_listesi').html(html);
    $('#devreden_aylar_yil_toplam').html(formatCurrency(yilToplam) + ' ₺');
}

// Currency format fonksiyonu
function formatCurrency(value) {
    return parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}
$(document).ready(function() {
    // Filtre değiştiğinde çalışacak fonksiyon
    function updateKasaPeriodLabel() {
        var zamanFiltresi = $('#zamana_gore_filtre_kasa').val();
        var label = $('#kasa_period_label');
        var dateRange = $('#kasa_date_range');

        // Özel tarih seçimi durumu
        if (zamanFiltresi === 'ozel') {
            var baslangic = $('#kasa_baslangic_tarihi').val();
            var bitis = $('#kasa_bitis_tarihi').val();

            if (baslangic && bitis) {
                // Tarih formatını düzenle
                var baslangicFormatted = formatDate(baslangic);
                var bitisFormatted = formatDate(bitis);

                label.text('Toplam Kasa');
                dateRange.text(baslangicFormatted + ' - ' + bitisFormatted).show();
            } else {
                label.text('Toplam Kasa (Özel)');
                dateRange.hide();
            }
        }
        // Bugün seçildiyse
        else if (zamanFiltresi.includes('/') && zamanFiltresi.split(' / ')[0] === zamanFiltresi.split(' / ')[1]) {
            var tarih = zamanFiltresi.split(' / ')[0];
            var tarihFormatted = formatDate(tarih);

            if (tarih === '{{date("Y-m-d")}}') {
                label.text('Günlük Toplam Kasa');
                dateRange.text('').show();
            } else {
                label.text('Günlük Toplam Kasa');
                dateRange.text('(' + tarihFormatted + ')').show();
            }
        }
        // Dün seçildiyse
        else if (zamanFiltresi.includes('/') && zamanFiltresi.split(' / ')[0] === zamanFiltresi.split(' / ')[1]) {
            var tarih = zamanFiltresi.split(' / ')[0];
            var tarihFormatted = formatDate(tarih);

            label.text('Günlük Toplam Kasa');
            dateRange.text('(' + tarihFormatted + ')').show();
        }
        // Bu ay seçildiyse
        else if (zamanFiltresi === '<?php echo date("Y-m-01") . " / " . date("Y-m-t"); ?>') {
            label.text('Aylık Toplam Kasa');
            dateRange.text('').show();
        }
        // Geçen ay seçildiyse
        else if (zamanFiltresi === '<?php echo date("Y-m-01",strtotime("-1 months")) . " / " . date("Y-m-t",strtotime("-1 months")); ?>') {
            label.text('Aylık Toplam Kasa');
            dateRange.text('').show();
        }
        // Bu yıl seçildiyse
        else if (zamanFiltresi === '<?php echo date("Y-01-01") . " / " . date("Y-12-31"); ?>') {
            label.text('Yıllık Toplam Kasa');
            dateRange.text('').show();
        }
        // Diğer durumlar
        else {
            label.text('Toplam Kasa');
            dateRange.hide();
        }
    }

    // Tarih formatlama fonksiyonu
    function formatDate(dateString) {
        var date = new Date(dateString);
        var day = date.getDate().toString().padStart(2, '0');
        var month = (date.getMonth() + 1).toString().padStart(2, '0');
        var year = date.getFullYear();
        return day + '.' + month + '.' + year;
    }

    // Sayfa yüklendiğinde etiketi güncelle
    updateKasaPeriodLabel();

    // Zaman filtresi değiştiğinde
    $('#zamana_gore_filtre_kasa').change(function() {
        updateKasaPeriodLabel();

        // Özel tarih seçimi göster/gizle
        if ($(this).val() === 'ozel') {
            $('#kasa_baslangic').show();
            $('#kasa_bitis').show();
        } else {
            $('#kasa_baslangic').hide();
            $('#kasa_bitis').hide();
        }
    });

    // Özel tarih değiştiğinde
    $('#kasa_baslangic_tarihi, #kasa_bitis_tarihi').change(function() {
        updateKasaPeriodLabel();
    });

    // AJAX ile filtreleme yapıldığında da etiketi güncelle
    $(document).on('kasaFiltreUygulandi', function(event, data) {
        // AJAX başarılı olduktan sonra etiketi güncelle
        setTimeout(function() {
            updateKasaPeriodLabel();
        }, 500);
    });
});
</script>
<!-- ============ GÜN SONU RAPORU MODAL ============ -->
<div id="gs-overlay" class="gs-overlay">
   <div class="gs-modal" role="dialog" aria-modal="true">
      <div class="gs-head">
         <div class="gs-head-left">
            <div class="gs-head-icon"><i class="fa fa-calendar-check-o"></i></div>
            <div>
               <h3 class="gs-title">Gün Sonu Raporu</h3>
               <div class="gs-subtitle" id="gs-tarih-etiket">Bugün</div>
            </div>
         </div>
         <div class="gs-head-right">
            <select id="gs-period" class="gs-period">
               <option value="bugun">Bugün</option>
               <option value="dun">Dün</option>
               <option value="buay">Bu ay</option>
               <option value="gecenay">Geçen ay</option>
               <option value="ozel">Özel tarih</option>
            </select>
            <div id="gs-ozel-wrap" class="gs-ozel-wrap" style="display:none">
               <input type="date" id="gs-b" class="gs-date">
               <span class="gs-date-sep">→</span>
               <input type="date" id="gs-bit" class="gs-date">
            </div>
            <button type="button" class="gs-print-btn" id="gs-detay-btn" title="Tam Detay / Sağlama"><i class="fa fa-list-ul"></i><span>Tam Detay</span></button>
            <button type="button" class="gs-print-btn" id="gs-print" title="PDF indir"><i class="fa fa-file-pdf-o"></i><span>PDF İndir</span></button>
            <button type="button" class="gs-close" id="gs-close" aria-label="Kapat">&times;</button>
         </div>
      </div>
      <div class="gs-body" id="gs-body">
         <div class="gs-loading"><i class="fa fa-spinner fa-spin"></i> Yükleniyor...</div>
      </div>
   </div>
</div>

<style>
.gs-overlay {
   display:none; position:fixed; inset:0; z-index:100000;
   background:rgba(15,23,42,.55); backdrop-filter:blur(2px);
   align-items:flex-start; justify-content:center; padding:24px 16px; overflow-y:auto;
}
.gs-overlay.show { display:flex; }
.gs-modal {
   background:#f6f7fb; width:100%; max-width:840px; border-radius:18px; overflow:hidden;
   box-shadow:0 24px 60px rgba(15,23,42,.35); display:flex; flex-direction:column;
   animation:gsIn .18s ease-out; margin:auto;
}
@keyframes gsIn { from{opacity:0; transform:translateY(14px) scale(.98);} to{opacity:1; transform:none;} }
.gs-head {
   display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;
   padding:16px 20px; background:linear-gradient(135deg,#5C008E 0%,#9D5DC8 100%);
}
.gs-head-left { display:flex; align-items:center; gap:12px; min-width:0; }
.gs-head-icon {
   width:42px; height:42px; border-radius:11px; background:rgba(255,255,255,.16);
   display:inline-flex; align-items:center; justify-content:center; color:#fff; font-size:18px; flex-shrink:0;
}
.gs-title { margin:0; font-size:17px; font-weight:700; color:#fff; }
.gs-subtitle { font-size:12.5px; color:#e9d6ff; margin-top:2px; }
.gs-head-right { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.gs-period, .gs-date {
   height:36px; border:none; border-radius:9px; padding:0 12px; font-size:13px; font-weight:600;
   color:#3b0764; background:#fff; cursor:pointer; outline:none;
}
.gs-ozel-wrap { display:flex; align-items:center; gap:6px; }
.gs-date { padding:0 8px; font-weight:500; }
.gs-date-sep { color:#fff; font-size:13px; }
.gs-print-btn {
   height:36px; padding:0 14px; border:none; border-radius:9px; cursor:pointer;
   background:rgba(255,255,255,.16); color:#fff; font-size:13px; font-weight:600;
   display:inline-flex; align-items:center; gap:7px; transition:background .15s;
}
.gs-print-btn:hover { background:rgba(255,255,255,.28); }
.gs-close {
   background:rgba(255,255,255,.16); border:none; color:#fff; width:36px; height:36px;
   border-radius:9px; font-size:22px; line-height:1; cursor:pointer; transition:background .15s;
}
.gs-close:hover { background:rgba(255,255,255,.3); }
.gs-body { padding:18px 20px 22px; overflow-y:auto; max-height:calc(100vh - 140px); }
.gs-loading { text-align:center; color:#64748b; padding:50px 0; font-size:14px; }

/* Ust ozet kartlari */
.gs-cards { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:14px; }
.gs-card { background:#fff; border:1px solid #eceef4; border-radius:14px; padding:16px 18px; position:relative; overflow:hidden; }
.gs-card::before { content:""; position:absolute; left:0; top:0; bottom:0; width:4px; }
.gs-card.net::before    { background:linear-gradient(180deg,#1d4ed8,#3b82f6); }
.gs-card.gelir::before  { background:linear-gradient(180deg,#0ea5b7,#22c1d6); }
.gs-card.masraf::before { background:linear-gradient(180deg,#dc2626,#ef4444); }
.gs-card .lbl { font-size:11.5px; font-weight:700; text-transform:uppercase; letter-spacing:.03em; color:#94a3b8; }
.gs-card .val { font-size:22px; font-weight:800; letter-spacing:-.5px; margin-top:4px; color:#1e293b; }
.gs-card.net .val.neg { color:#dc2626; }
.gs-card.net .val.pos { color:#15803d; }

/* Mini istatistik seridi */
.gs-mini { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:14px; }
.gs-mini-item { background:#fff; border:1px solid #eceef4; border-radius:12px; padding:12px 14px; text-align:center; }
.gs-mini-item .n { font-size:19px; font-weight:800; color:#3b0764; }
.gs-mini-item .t { font-size:11.5px; font-weight:600; color:#64748b; margin-top:2px; }

/* Bolum basligi */
.gs-section { font-size:12.5px; font-weight:700; text-transform:uppercase; letter-spacing:.04em;
   color:#475569; margin:18px 0 10px; display:flex; align-items:center; gap:8px; }
.gs-section i { color:#9D5DC8; }

/* Odeme yontemi tablosu */
.gs-tablewrap { background:#fff; border:1px solid #eceef4; border-radius:14px; overflow:hidden; }
.gs-ptable { width:100%; border-collapse:collapse; }
.gs-ptable th, .gs-ptable td { padding:10px 14px; font-size:13px; text-align:right; }
.gs-ptable th { background:#f5eefe; color:#5C008E; font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:.03em; }
.gs-ptable th:first-child, .gs-ptable td:first-child { text-align:left; font-weight:600; color:#1e293b; }
.gs-ptable tbody td { border-top:1px solid #f1f0f6; color:#334155; }
.gs-ptable tbody tr:hover { background:#faf7ff; }
.gs-ptable .pos { color:#15803d; font-weight:700; }
.gs-ptable .neg { color:#dc2626; font-weight:700; }
.gs-ptable tfoot td { border-top:2px solid #ead4ff; font-weight:800; color:#1e293b; background:#fcfaff; }

/* Ikili liste (personel + urun/hizmet) */
.gs-list { background:#fff; border:1px solid #eceef4; border-radius:14px; overflow:hidden; }
.gs-list table { width:100%; border-collapse:collapse; }
.gs-list th, .gs-list td { padding:9px 14px; font-size:12.5px; }
.gs-list th { background:#f8fafc; color:#64748b; font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:.03em; text-align:left; }
.gs-list th.r, .gs-list td.r { text-align:right; }
.gs-list td { border-top:1px solid #f1f0f6; color:#334155; }
.gs-list td.ad { font-weight:600; color:#1e293b; }
.gs-list td.ciro { font-weight:700; color:#5C008E; }
.gs-two { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.gs-empty { text-align:center; color:#94a3b8; font-size:12.5px; padding:16px 0; }

/* Tam Detay: liste alt toplam + rozetler + saglama */
.gs-list tfoot td { border-top:2px solid #ead4ff; background:#fcfaff; font-weight:800; color:#1e293b; font-size:12.5px; padding:9px 14px; }
.gs-tag { display:inline-block; font-size:10px; font-weight:700; border-radius:6px; padding:1px 7px; color:#fff; white-space:nowrap; }
.gs-tag.in { background:#0ea5b7; }   /* Para Girisi */
.gs-tag.pg { background:#f59e0b; }   /* Personel Gideri */
.gs-tag.po { background:#6366f1; }   /* Personel Odemesi */
.gs-saglama { background:#fff; border:1px solid #eceef4; border-radius:14px; padding:14px 18px; margin-top:16px; }
.gs-saglama .sg-row { display:flex; align-items:center; justify-content:space-between; padding:6px 0; font-size:13.5px; color:#475569; }
.gs-saglama .sg-row b { color:#1e293b; font-weight:700; }
.gs-saglama .sg-row.total { border-top:2px dashed #ead4ff; margin-top:6px; padding-top:12px; font-size:16px; font-weight:800; color:#1e293b; }
.gs-saglama .sg-row.total b.pos { color:#15803d; }
.gs-saglama .sg-row.total b.neg { color:#dc2626; }

@media (max-width:640px){
   .gs-head-right { width:100%; }
   .gs-cards { grid-template-columns:1fr; }
   .gs-mini { grid-template-columns:repeat(3,1fr); }
   .gs-two { grid-template-columns:1fr; }
   .gs-card .val { font-size:20px; }
}
</style>

<script>
(function(){
   var GS_BUGUN = '{{date("Y-m-d")}}';
   var $ov = $('#gs-overlay');
   $ov.appendTo('body');
   var gsData = null;
   var gsDetay = false; // false=Özet, true=Tam Detay (sağlama)

   function esc(s){ return $('<div>').text(s==null?'':s).html(); }
   function gsFmt(n){ n = Number(n)||0; return n.toLocaleString('tr-TR',{minimumFractionDigits:2,maximumFractionDigits:2}); }
   function pad(n){ return (n<10?'0':'')+n; }
   function iso(d){ return d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate()); }
   function trTarih(s){ if(!s) return ''; var p=s.split('-'); return p[2]+'.'+p[1]+'.'+p[0]; }

   // Secili donemden [baslangic, bitis] uret
   function gsAralik(){
      var p = $('#gs-period').val();
      var t = new Date(GS_BUGUN+'T00:00:00');
      if(p==='bugun') return [iso(t), iso(t)];
      if(p==='dun'){ var y=new Date(t); y.setDate(y.getDate()-1); return [iso(y), iso(y)]; }
      if(p==='buay'){ var b=new Date(t.getFullYear(),t.getMonth(),1); return [iso(b), iso(t)]; }
      if(p==='gecenay'){ var b=new Date(t.getFullYear(),t.getMonth()-1,1); var e=new Date(t.getFullYear(),t.getMonth(),0); return [iso(b), iso(e)]; }
      // ozel
      return [$('#gs-b').val()||iso(t), $('#gs-bit').val()||iso(t)];
   }

   function gsEtiket(b,bit){
      if(b===bit) return trTarih(b);
      return trTarih(b)+' → '+trTarih(bit);
   }

   function gsDetayBtnGuncelle(){
      $('#gs-detay-btn span').text(gsDetay ? 'Özet' : 'Tam Detay');
      $('#gs-detay-btn i').attr('class', gsDetay ? 'fa fa-th-large' : 'fa fa-list-ul');
   }
   window.gunSonuAc = function(){
      $('#gs-period').val('bugun');
      $('#gs-ozel-wrap').hide();
      $('#gs-b').val(GS_BUGUN); $('#gs-bit').val(GS_BUGUN);
      gsDetay = false; gsDetayBtnGuncelle();
      $ov.addClass('show');
      gsYukle();
   };
   function gsKapat(){ $ov.removeClass('show'); }
   $('#gs-close').on('click', gsKapat);
   $ov.on('click', function(e){ if(e.target===this) gsKapat(); });
   $(document).on('keydown', function(e){ if(e.key==='Escape' && $ov.hasClass('show')) gsKapat(); });

   // Özet <-> Tam Detay gecisi (yeni AJAX yok, mevcut veriyi yeniden cizer)
   $('#gs-detay-btn').on('click', function(){
      gsDetay = !gsDetay; gsDetayBtnGuncelle();
      if(gsData) $('#gs-body').html(gsRender(gsData));
      $('#gs-body').scrollTop(0);
   });

   $('#gs-period').on('change', function(){
      if($(this).val()==='ozel'){ $('#gs-ozel-wrap').css('display','flex'); }
      else { $('#gs-ozel-wrap').hide(); gsYukle(); }
   });
   $('#gs-b, #gs-bit').on('change', function(){ if($('#gs-period').val()==='ozel') gsYukle(); });

   function gsYukle(){
      var a = gsAralik();
      $('#gs-tarih-etiket').text(gsEtiket(a[0],a[1]));
      $('#gs-body').html('<div class="gs-loading"><i class="fa fa-spinner fa-spin"></i> Yükleniyor...</div>');
      $.ajax({
         url:'/isletmeyonetim/gunsonuraporu', type:'GET', dataType:'json',
         data:{ baslangic:a[0], bitis:a[1], sube:$('input[name="sube"]').val() },
         success:function(d){
            if(!d || d.durum!=='ok'){ $('#gs-body').html('<div class="gs-empty">Rapor yüklenemedi.</div>'); return; }
            gsData = d; $('#gs-body').html(gsRender(d));
         },
         error:function(){ $('#gs-body').html('<div class="gs-empty">Rapor yüklenirken bir hata oluştu.</div>'); }
      });
   }

   var YONTEMLER = [['nakit','Nakit'],['kredikarti','Kredi Kartı'],['havale','Havale'],['online','Online'],['diger','Diğer']];

   function gsRender(d){
      if(gsDetay) return gsRenderDetay(d);
      var netCls = d.net_toplam < 0 ? 'neg' : 'pos';
      var h = '';

      // Ust kartlar (soldan saga: Gelir - Masraf = Net; Net en sagda)
      h += '<div class="gs-cards">';
      h += '<div class="gs-card gelir"><div class="lbl">Gelirler Toplamı</div><div class="val">'+gsFmt(d.gelir_toplam)+' ₺</div></div>';
      h += '<div class="gs-card masraf"><div class="lbl">Masraflar Toplamı</div><div class="val">'+gsFmt(d.masraf_toplam)+' ₺</div></div>';
      h += '<div class="gs-card net"><div class="lbl">Net (Gelir − Masraf)</div><div class="val '+netCls+'">'+gsFmt(d.net_toplam)+' ₺</div></div>';
      h += '</div>';

      // Mini istatistikler
      h += '<div class="gs-mini">';
      h += '<div class="gs-mini-item"><div class="n">'+d.islem_say+'</div><div class="t">İşlem Sayısı</div></div>';
      h += '<div class="gs-mini-item"><div class="n">'+d.musteri_say+'</div><div class="t">Müşteri Sayısı</div></div>';
      h += '<div class="gs-mini-item"><div class="n">'+gsFmt(d.ort_sepet)+' ₺</div><div class="t">Ortalama Sepet</div></div>';
      h += '</div>';

      // Odeme yontemi kirilimi tablosu
      h += '<div class="gs-section"><i class="fa fa-credit-card"></i> Ödeme Yöntemine Göre</div>';
      h += '<div class="gs-tablewrap"><table class="gs-ptable"><thead><tr><th>Yöntem</th><th>Gelir</th><th>Masraf</th><th>Net</th></tr></thead><tbody>';
      YONTEMLER.forEach(function(y){
         var k=y[0], nv=d.net[k];
         h += '<tr><td>'+y[1]+'</td><td>'+gsFmt(d.gelir[k])+'</td><td>'+gsFmt(d.masraf[k])+'</td>'+
              '<td class="'+(nv<0?'neg':'pos')+'">'+gsFmt(nv)+'</td></tr>';
      });
      h += '</tbody><tfoot><tr><td>Toplam</td><td>'+gsFmt(d.gelir_toplam)+'</td><td>'+gsFmt(d.masraf_toplam)+
           '</td><td class="'+(d.net_toplam<0?'neg':'pos')+'">'+gsFmt(d.net_toplam)+'</td></tr></tfoot></table></div>';

      // Personel cirosu
      h += '<div class="gs-section"><i class="fa fa-users"></i> Personel Bazlı Ciro</div>';
      h += '<div class="gs-list"><table><thead><tr><th>Personel</th><th class="r">İşlem</th><th class="r">Ciro</th></tr></thead><tbody>';
      if(d.personeller && d.personeller.length){
         d.personeller.forEach(function(p){
            h += '<tr><td class="ad">'+esc(p.personel_adi)+'</td><td class="r">'+p.adet+'</td><td class="r ciro">'+gsFmt(p.ciro)+' ₺</td></tr>';
         });
      } else { h += '<tr><td colspan="3"><div class="gs-empty">Bu dönemde personel işlemi bulunamadı.</div></td></tr>'; }
      h += '</tbody></table></div>';

      // En cok satan hizmet / urun
      h += '<div class="gs-two">';
      h += '<div><div class="gs-section"><i class="fa fa-scissors"></i> En Çok Satan Hizmet</div>'+gsMiniList(d.top_hizmet,'hizmet_adi')+'</div>';
      h += '<div><div class="gs-section"><i class="fa fa-shopping-bag"></i> En Çok Satan Ürün</div>'+gsMiniList(d.top_urun,'urun_adi')+'</div>';
      h += '</div>';

      return h;
   }

   // ---- TAM DETAY (SAĞLAMA) GÖRÜNÜMÜ ----
   function gsRenderDetay(d){
      var h = '';

      // 1) Satislar (Gelirler)
      h += '<div class="gs-section"><i class="fa fa-shopping-cart"></i> Satışlar (Gelirler)</div>';
      h += '<div class="gs-list"><table><thead><tr><th>Saat</th><th>Müşteri</th><th>Tahsil Eden</th><th>Yöntem</th><th class="r">Tutar</th></tr></thead><tbody>';
      if(d.satislar && d.satislar.length){
         d.satislar.forEach(function(s){
            h += '<tr><td>'+esc(s.saat)+'</td>'+
                 '<td class="ad">'+esc(s.musteri)+(s.para_girisi?' <span class="gs-tag in">Para Girişi</span>':'')+'</td>'+
                 '<td>'+esc(s.tahsil_eden)+'</td><td>'+esc(s.yontem)+'</td>'+
                 '<td class="r ciro">'+gsFmt(s.tutar)+' ₺</td></tr>';
         });
      } else { h += '<tr><td colspan="5"><div class="gs-empty">Bu dönemde satış yok.</div></td></tr>'; }
      h += '</tbody><tfoot><tr><td colspan="4">Satışlar Toplamı</td><td class="r">'+gsFmt(d.gelir_toplam)+' ₺</td></tr></tfoot></table></div>';

      // 2) Isletme Masraflari
      h += '<div class="gs-section"><i class="fa fa-arrow-down"></i> İşletme Masrafları</div>';
      h += '<div class="gs-list"><table><thead><tr><th>Harcayan</th><th>Açıklama</th><th>Yöntem</th><th class="r">Tutar</th></tr></thead><tbody>';
      if(d.isletme_masraflari && d.isletme_masraflari.length){
         d.isletme_masraflari.forEach(function(m){
            h += '<tr><td class="ad">'+esc(m.harcayan)+'</td><td>'+esc(m.aciklama||'-')+'</td><td>'+esc(m.yontem)+'</td><td class="r ciro">'+gsFmt(m.tutar)+' ₺</td></tr>';
         });
      } else { h += '<tr><td colspan="4"><div class="gs-empty">İşletme masrafı yok.</div></td></tr>'; }
      h += '</tbody><tfoot><tr><td colspan="3">İşletme Masrafları Toplamı</td><td class="r">'+gsFmt(d.isletme_masraf_toplam)+' ₺</td></tr></tfoot></table></div>';

      // 3) Personel Giderleri & Odemeleri
      h += '<div class="gs-section"><i class="fa fa-user"></i> Personel Giderleri &amp; Ödemeleri</div>';
      h += '<div class="gs-list"><table><thead><tr><th>Personel</th><th>Tür</th><th>Açıklama</th><th class="r">Tutar</th></tr></thead><tbody>';
      if(d.personel_giderleri && d.personel_giderleri.length){
         d.personel_giderleri.forEach(function(m){
            var tagCls = m.tip==='Personel Gideri' ? 'pg' : 'po';
            h += '<tr><td class="ad">'+esc(m.harcayan)+'</td><td><span class="gs-tag '+tagCls+'">'+esc(m.tip)+'</span></td><td>'+esc(m.aciklama||'-')+'</td><td class="r ciro">'+gsFmt(m.tutar)+' ₺</td></tr>';
         });
      } else { h += '<tr><td colspan="4"><div class="gs-empty">Personel gideri/ödemesi yok.</div></td></tr>'; }
      h += '</tbody><tfoot><tr><td colspan="3">Personel Giderleri Toplamı</td><td class="r">'+gsFmt(d.personel_gider_toplam)+' ₺</td></tr></tfoot></table></div>';

      // 4) Saglama
      var netCls = d.net_toplam<0?'neg':'pos';
      h += '<div class="gs-saglama">'+
           '<div class="sg-row"><span>Satışlar</span><b>'+gsFmt(d.gelir_toplam)+' ₺</b></div>'+
           '<div class="sg-row"><span>− İşletme Masrafları</span><b>'+gsFmt(d.isletme_masraf_toplam)+' ₺</b></div>'+
           '<div class="sg-row"><span>− Personel Giderleri</span><b>'+gsFmt(d.personel_gider_toplam)+' ₺</b></div>'+
           '<div class="sg-row total"><span>= NET (Kasa)</span><b class="'+netCls+'">'+gsFmt(d.net_toplam)+' ₺</b></div>'+
           '</div>';
      return h;
   }

   function gsMiniList(rows, adKey){
      var h = '<div class="gs-list"><table><thead><tr><th>Ad</th><th class="r">Adet</th><th class="r">Ciro</th></tr></thead><tbody>';
      if(rows && rows.length){
         rows.forEach(function(r){
            h += '<tr><td class="ad">'+esc(r[adKey])+'</td><td class="r">'+r.adet+'</td><td class="r ciro">'+gsFmt(r.ciro)+' ₺</td></tr>';
         });
      } else { h += '<tr><td colspan="3"><div class="gs-empty">Kayıt yok.</div></td></tr>'; }
      h += '</tbody></table></div>';
      return h;
   }

   // pdfmake dinamik yukleyici (sadece Indir'e ilk basildiginda ~ agirligi ceker,
   // 401/402 disi sayfalarda layout'ta yuklenmiyor)
   function gsPdfHazir(cb){
      if(window.pdfMake && window.pdfMake.createPdf){ cb(); return; }
      var s1 = document.createElement('script');
      s1.src = "{{secure_asset('public/yeni_panel/src/plugins/datatables/js/pdfmake.min.js')}}";
      s1.onload = function(){
         var s2 = document.createElement('script');
         s2.src = "{{secure_asset('public/yeni_panel/src/plugins/datatables/js/vfs_fonts.js')}}";
         s2.onload = function(){ cb(); };
         s2.onerror = function(){ alert('PDF kütüphanesi yüklenemedi (font).'); };
         document.body.appendChild(s2);
      };
      s1.onerror = function(){ alert('PDF kütüphanesi yüklenemedi.'); };
      document.body.appendChild(s1);
   }

   function gsPdfOlustur(){
      var d = gsData;
      var salon = @json($isletme->salon_adi);
      var don = gsEtiket(d.baslangic, d.bitis);
      var MOR = '#5C008E';
      // Hucre yardimcilari (L=sol, R=sag; b=kalin)
      function L(v,b){ return {text:String(v), bold:!!b}; }
      function R(v,b){ return {text:String(v), alignment:'right', bold:!!b}; }
      function head(arr){ return arr.map(function(h,i){ return {text:h, style:'th', alignment:(i===0?'left':'right')}; }); }
      function sec(t){ return {table:{widths:['*'], body:[[{text:t, color:'#fff', fillColor:MOR, bold:true, fontSize:10, margin:[5,3,5,3]}]]}, layout:'noBorders', margin:[0,9,0,3]}; }
      function tbl(widths, body){ return {table:{widths:widths, body:body}, layout:'lightHorizontalLines', margin:[0,0,0,3]}; }
      function tblH(widths, headArr, body){ return {table:{headerRows:1, widths:widths, body:[head(headArr)].concat(body)}, layout:'lightHorizontalLines', margin:[0,0,0,3]}; }
      function bosSat(n, txt){ var r=[]; for(var i=0;i<n;i++) r.push({}); r[0]={text:txt, colSpan:n, alignment:'center', color:'#999', italics:true}; return r; }

      var c = [];
      c.push({text:'Gün Sonu Raporu', style:'h1'});
      c.push({text: salon+'   -   '+don, style:'sub', margin:[0,2,0,6]});

      // Ozet
      c.push(sec('ÖZET'));
      c.push(tbl(['*','auto'], [
         [L('Gelirler Toplamı'), R(gsFmt(d.gelir_toplam)+' TL')],
         [L('Masraflar Toplamı'), R(gsFmt(d.masraf_toplam)+' TL')],
         [L('Net (Gelir - Masraf)',true), R(gsFmt(d.net_toplam)+' TL',true)],
         [L('İşlem Sayısı'), R(d.islem_say)],
         [L('Müşteri Sayısı'), R(d.musteri_say)],
         [L('Ortalama Sepet'), R(gsFmt(d.ort_sepet)+' TL')]
      ]));

      // Odeme yontemine gore
      c.push(sec('ÖDEME YÖNTEMİNE GÖRE'));
      var pmB = [];
      YONTEMLER.forEach(function(y){ var k=y[0]; pmB.push([L(y[1]), R(gsFmt(d.gelir[k])), R(gsFmt(d.masraf[k])), R(gsFmt(d.net[k]))]); });
      pmB.push([L('Toplam',true), R(gsFmt(d.gelir_toplam),true), R(gsFmt(d.masraf_toplam),true), R(gsFmt(d.net_toplam),true)]);
      c.push(tblH(['*','auto','auto','auto'], ['Yöntem','Gelir','Masraf','Net'], pmB));

      if(gsDetay){
         // Satislar
         c.push(sec('SATIŞLAR (GELİRLER)'));
         var sB = [];
         (d.satislar||[]).forEach(function(s){ sB.push([L(s.saat), L((s.musteri||'')+(s.para_girisi?' (Para Girişi)':'')), L(s.tahsil_eden||''), L(s.yontem||''), R(gsFmt(s.tutar))]); });
         if(!sB.length) sB.push(bosSat(5,'Satış yok'));
         sB.push([{text:'Satışlar Toplamı', colSpan:4, bold:true},{},{},{}, R(gsFmt(d.gelir_toplam),true)]);
         c.push(tblH(['auto','*','auto','auto','auto'], ['Saat','Müşteri','Tahsil Eden','Yöntem','Tutar'], sB));
         // Isletme masraflari
         c.push(sec('İŞLETME MASRAFLARI'));
         var iB = [];
         (d.isletme_masraflari||[]).forEach(function(m){ iB.push([L(m.harcayan||''), L(m.aciklama||'-'), L(m.yontem||''), R(gsFmt(m.tutar))]); });
         if(!iB.length) iB.push(bosSat(4,'İşletme masrafı yok'));
         iB.push([{text:'İşletme Masrafları Toplamı', colSpan:3, bold:true},{},{}, R(gsFmt(d.isletme_masraf_toplam),true)]);
         c.push(tblH(['*','*','auto','auto'], ['Harcayan','Açıklama','Yöntem','Tutar'], iB));
         // Personel giderleri
         c.push(sec('PERSONEL GİDERLERİ & ÖDEMELERİ'));
         var pB = [];
         (d.personel_giderleri||[]).forEach(function(m){ pB.push([L(m.harcayan||''), L(m.tip||''), L(m.aciklama||'-'), R(gsFmt(m.tutar))]); });
         if(!pB.length) pB.push(bosSat(4,'Personel gideri/ödemesi yok'));
         pB.push([{text:'Personel Giderleri Toplamı', colSpan:3, bold:true},{},{}, R(gsFmt(d.personel_gider_toplam),true)]);
         c.push(tblH(['*','auto','*','auto'], ['Personel','Tür','Açıklama','Tutar'], pB));
         // Saglama
         c.push(sec('SAĞLAMA'));
         c.push(tbl(['*','auto'], [
            [L('Satışlar'), R(gsFmt(d.gelir_toplam)+' TL')],
            [L('(-) İşletme Masrafları'), R(gsFmt(d.isletme_masraf_toplam)+' TL')],
            [L('(-) Personel Giderleri'), R(gsFmt(d.personel_gider_toplam)+' TL')],
            [L('= NET (Kasa)',true), R(gsFmt(d.net_toplam)+' TL',true)]
         ]));
      } else {
         // Personel ciro
         c.push(sec('PERSONEL BAZLI CİRO'));
         var perB = [];
         (d.personeller||[]).forEach(function(p){ perB.push([L(p.personel_adi||''), R(p.adet), R(gsFmt(p.ciro))]); });
         if(!perB.length) perB.push(bosSat(3,'Kayıt yok'));
         c.push(tblH(['*','auto','auto'], ['Personel','İşlem','Ciro'], perB));
         // En cok satan hizmet
         c.push(sec('EN ÇOK SATAN HİZMET'));
         var hB = [];
         (d.top_hizmet||[]).forEach(function(r){ hB.push([L(r.hizmet_adi||''), R(r.adet), R(gsFmt(r.ciro))]); });
         if(!hB.length) hB.push(bosSat(3,'Kayıt yok'));
         c.push(tblH(['*','auto','auto'], ['Ad','Adet','Ciro'], hB));
         // En cok satan urun
         c.push(sec('EN ÇOK SATAN ÜRÜN'));
         var uB = [];
         (d.top_urun||[]).forEach(function(r){ uB.push([L(r.urun_adi||''), R(r.adet), R(gsFmt(r.ciro))]); });
         if(!uB.length) uB.push(bosSat(3,'Kayıt yok'));
         c.push(tblH(['*','auto','auto'], ['Ad','Adet','Ciro'], uB));
      }

      var docDef = {
         pageMargins:[26,26,26,34],
         content: c,
         defaultStyle:{ fontSize:9, color:'#333' },
         styles:{
            h1:{ fontSize:16, bold:true, color:MOR },
            sub:{ fontSize:9, color:'#666' },
            th:{ bold:true, fontSize:8.5, color:MOR, fillColor:'#f5eefe' }
         },
         footer: function(cur, tot){ return {text: cur+' / '+tot, alignment:'center', fontSize:7.5, color:'#999', margin:[0,6,0,0]}; }
      };
      var fn = 'gun-sonu-'+d.baslangic+(d.baslangic!==d.bitis?'_'+d.bitis:'')+(gsDetay?'-detay':'')+'.pdf';
      pdfMake.createPdf(docDef).download(fn);
   }

   // Indir (PDF) — mevcut goruntuye gore (Özet / Tam Detay saglama)
   $('#gs-print').on('click', function(){
      if(!gsData) return;
      var $btn = $(this); var eski = $btn.find('span').text();
      $btn.prop('disabled', true).find('span').text('Hazırlanıyor...');
      gsPdfHazir(function(){
         try { gsPdfOlustur(); }
         catch(e){ alert('PDF oluşturulamadı: '+(e && e.message ? e.message : e)); }
         $btn.prop('disabled', false).find('span').text(eski);
      });
   });
})();
</script>

@endsection
