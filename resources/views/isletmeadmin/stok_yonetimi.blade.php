@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

<style>
:root {
  --stk-mor: #6A1B9A;
  --stk-mor-koyu: #4A148C;
  --stk-mor-soft: #F3E8FA;
  --stk-yesil: #43A047;
  --stk-yesil-soft: #E8F5E9;
  --stk-sari: #F6A609;
  --stk-sari-soft: #FFF8E1;
  --stk-kirmizi: #E53935;
  --stk-kirmizi-soft: #FFEBEE;
  --stk-mavi: #1565C0;
  --stk-mavi-soft: #E3F2FD;
  --stk-pembe: #AD1457;
  --stk-pembe-soft: #FCE4EC;
  --stk-zemin: #F7F7FB;
  --stk-cizgi: #ECECF2;
}

/* === ÖZET KARTLARI === */
.stk-ozet-kart { background: #fff; border-radius: 14px; padding: 18px 20px; box-shadow: 0 2px 12px rgba(0,0,0,0.05); border: 1px solid #f0f0f5; transition: transform .2s, box-shadow .2s; }
.stk-ozet-kart:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(106,27,154,0.10); }
.stk-ozet-kart .stk-ikon-kutu { width: 38px; height: 38px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; }
.stk-ozet-kart .baslik { font-size: 11px; color: #8a8a9a; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; }
.stk-ozet-kart .deger { font-size: 26px; font-weight: 800; margin-top: 4px; color: #1a1a2e; }
.stk-ozet-kart.mor     { border-top: 3px solid var(--stk-mor); }
.stk-ozet-kart.sari    { border-top: 3px solid var(--stk-sari); }
.stk-ozet-kart.kirmizi { border-top: 3px solid var(--stk-kirmizi); }
.stk-ozet-kart.yesil   { border-top: 3px solid var(--stk-yesil); }

/* === ROZET & CHIP === */
.stk-rozet { display:inline-block; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; }
.stk-rozet.yesil  { background: var(--stk-yesil-soft); color: #2E7D32; }
.stk-rozet.sari   { background: var(--stk-sari-soft); color: #B27300; }
.stk-rozet.kirmizi{ background: var(--stk-kirmizi-soft); color: #C62828; }
.stk-tip-chip { font-size: 10px; padding: 3px 9px; border-radius: 5px; background: #ECEFF1; color: #455A64; display:inline-block; font-weight: 800; letter-spacing: 0.4px; }
.stk-tip-chip.satis { background: var(--stk-mavi-soft); color: var(--stk-mavi); }
.stk-tip-chip.sarf  { background: var(--stk-pembe-soft); color: var(--stk-pembe); }
.stk-tip-chip.karma { background: var(--stk-mor-soft); color: var(--stk-mor); }

/* === AKSİYON BUTONLARI === */
.stk-aksiyon-btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 9px 16px; border-radius: 10px; font-weight: 600; font-size: 13px;
  border: none; transition: all .2s; cursor: pointer; margin-right: 6px; margin-bottom: 6px;
}
.stk-aksiyon-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.12); }
.stk-aksiyon-btn.primary  { background: linear-gradient(135deg, var(--stk-mor), #8E24AA); color: #fff; }
.stk-aksiyon-btn.success  { background: linear-gradient(135deg, var(--stk-yesil), #66BB6A); color: #fff; }
.stk-aksiyon-btn.warning  { background: linear-gradient(135deg, var(--stk-sari), #FFB74D); color: #fff; }
.stk-aksiyon-btn.info     { background: linear-gradient(135deg, #1976D2, #42A5F5); color: #fff; }
.stk-aksiyon-btn.dark     { background: #455A64; color: #fff; }
.stk-aksiyon-btn.outline  { background: #fff; border: 1.5px solid #e0e0e7; color: #455A64; }

/* === ÜRÜN TABLOSU === */
.stk-tablo-kart { background: #fff; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,0.04); overflow: hidden; }
.stk-tablo-filter { padding: 14px 18px; background: #FAFAFC; border-bottom: 1px solid var(--stk-cizgi); }
.stk-form-input { border: 1.5px solid #e0e0e7; border-radius: 10px; padding: 9px 14px; transition: border-color .2s, box-shadow .2s; font-size: 14px; width: 100%; background: #fff; }
.stk-form-input:focus { border-color: var(--stk-mor); box-shadow: 0 0 0 3px rgba(106,27,154,0.10); outline: none; }
.stk-form-input::placeholder { color: #a0a0b0; }
.stk-form-select { border: 1.5px solid #e0e0e7; border-radius: 10px; padding: 9px 14px; background: #fff; font-size: 14px; width: 100%; }
.stk-form-select:focus { border-color: var(--stk-mor); outline: none; }
#urun_tablosu { width: 100%; }
#urun_tablosu thead th { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #8a8a9a; font-weight: 700; padding: 14px 12px; border-bottom: 2px solid var(--stk-cizgi); background: #FAFAFC; }
#urun_tablosu tbody td { padding: 14px 12px; border-bottom: 1px solid var(--stk-cizgi); vertical-align: middle; font-size: 14px; }
#urun_tablosu tbody tr:hover { background: #FAFAFC; }
.urun-aksiyon-btn { padding: 6px 10px; font-size: 12px; border-radius: 7px; border: 1px solid transparent; background: #fff; transition: all .15s; cursor: pointer; margin: 0 2px; }
.urun-aksiyon-btn.duz  { color: var(--stk-mor);     border-color: var(--stk-mor-soft); }
.urun-aksiyon-btn.duz:hover  { background: var(--stk-mor-soft); }
.urun-aksiyon-btn.gec  { color: var(--stk-mavi);    border-color: var(--stk-mavi-soft); }
.urun-aksiyon-btn.gec:hover  { background: var(--stk-mavi-soft); }
.urun-aksiyon-btn.sil  { color: var(--stk-kirmizi); border-color: var(--stk-kirmizi-soft); }
.urun-aksiyon-btn.sil:hover  { background: var(--stk-kirmizi-soft); }

/* === PREMIUM MODAL === */
.stk-modal .modal-dialog { margin-top: 30px; }
.stk-modal .modal-content { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,0.15); }
.stk-modal .modal-header {
  background: linear-gradient(135deg, var(--stk-mor) 0%, var(--stk-mor-koyu) 100%);
  color: #fff; padding: 18px 24px; border: none;
}
.stk-modal .modal-header h5 { color: #fff; font-weight: 800; font-size: 18px; margin: 0; display: flex; align-items: center; gap: 10px; }
.stk-modal .modal-header .close {
  color: #fff; opacity: .85; text-shadow: none; font-size: 26px; font-weight: 300;
  background: rgba(255,255,255,0.15); border-radius: 50%; width: 32px; height: 32px;
  line-height: 32px; padding: 0; transition: all .15s;
}
.stk-modal .modal-header .close:hover { opacity: 1; background: rgba(255,255,255,0.25); }
.stk-modal .modal-body { padding: 24px; background: #FAFAFC; }
.stk-modal .modal-footer {
  background: #fff; border-top: 1px solid var(--stk-cizgi);
  padding: 14px 24px; display: flex; justify-content: flex-end; gap: 8px;
}
.stk-modal .modal-footer .stk-aksiyon-btn { margin: 0; }

/* === FORM BÖLÜMLERİ (modal içinde) === */
.stk-bolum {
  background: #fff; border-radius: 12px; padding: 16px 18px; margin-bottom: 14px;
  border: 1px solid var(--stk-cizgi);
}
.stk-bolum-baslik { font-size: 11px; text-transform: uppercase; letter-spacing: 0.6px; color: var(--stk-mor); font-weight: 800; margin-bottom: 12px; }
.stk-form-grup { margin-bottom: 12px; }
.stk-form-grup label { font-size: 12px; font-weight: 700; color: #455A64; margin-bottom: 4px; display: block; letter-spacing: 0.2px; }
.stk-form-grup label .opsiyonel { color: #a0a0b0; font-weight: 500; font-size: 11px; margin-left: 4px; }
.stk-form-grup label .zorunlu { color: var(--stk-kirmizi); margin-left: 2px; }
.stk-form-grup .form-control, .stk-form-grup select.form-control {
  border: 1.5px solid #e0e0e7; border-radius: 10px; padding: 9px 14px;
  font-size: 14px; box-shadow: none; transition: all .15s; height: auto;
}
.stk-form-grup .form-control:focus { border-color: var(--stk-mor); box-shadow: 0 0 0 3px rgba(106,27,154,0.10); }

/* === SEGMENTED TIP === */
.stk-segment { background: #F3E8FA; border-radius: 10px; padding: 4px; display: flex; gap: 4px; }
.stk-segment-btn {
  flex: 1; padding: 8px 12px; border-radius: 7px; border: none; background: transparent;
  font-size: 13px; font-weight: 700; color: #6A1B9A; cursor: pointer; transition: all .15s;
}
.stk-segment-btn.aktif { background: var(--stk-mor); color: #fff; box-shadow: 0 2px 8px rgba(106,27,154,0.3); }

/* === SATIR KARTLARI (liste içi) === */
.stk-satir-kart {
  background: #fff; border-radius: 10px; padding: 12px 14px; margin-bottom: 8px;
  border: 1px solid var(--stk-cizgi); display: flex; align-items: center; gap: 12px;
  transition: all .15s;
}
.stk-satir-kart:hover { border-color: var(--stk-mor-soft); transform: translateX(2px); }
.stk-satir-ikon-kutu { width: 36px; height: 36px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stk-satir-baslik { font-weight: 700; color: #1a1a2e; font-size: 14px; }
.stk-satir-alt    { color: #8a8a9a; font-size: 12px; margin-top: 2px; }
.stk-satir-mini-btn { padding: 6px 10px; border-radius: 7px; border: 1px solid; font-size: 12px; background: #fff; transition: all .15s; cursor: pointer; }
.stk-satir-mini-btn.duz { color: var(--stk-mor);     border-color: var(--stk-mor-soft); }
.stk-satir-mini-btn.duz:hover { background: var(--stk-mor-soft); }
.stk-satir-mini-btn.sil { color: var(--stk-kirmizi); border-color: var(--stk-kirmizi-soft); }
.stk-satir-mini-btn.sil:hover { background: var(--stk-kirmizi-soft); }

/* === SEPET (Hızlı Satış) === */
.sepet-satir {
  display: flex; align-items: center; gap: 10px; padding: 10px 12px;
  border-bottom: 1px solid var(--stk-cizgi); transition: background .15s;
}
.sepet-satir:hover { background: #FAFAFC; }
.sepet-satir .miktar-input { width: 70px; text-align: center; border: 1.5px solid #e0e0e7; border-radius: 8px; padding: 6px; font-weight: 700; }
.sepet-toplam-kutu {
  background: linear-gradient(135deg, var(--stk-mor), #8E24AA); color: #fff;
  padding: 14px 18px; border-radius: 12px; margin-top: 12px;
  display: flex; justify-content: space-between; align-items: center;
}
.sepet-toplam-kutu .tutar { font-size: 26px; font-weight: 800; }

/* === TOAST BİLDİRİMLERİ === */
#stk-toast-konteyner { position: fixed; top: 20px; right: 20px; z-index: 99999; pointer-events: none; }
.stk-toast {
  pointer-events: auto; min-width: 280px; max-width: 380px; margin-bottom: 10px;
  padding: 12px 16px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.15);
  color: #fff; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 10px;
  animation: stk-toast-in .25s ease-out;
}
.stk-toast.gosterilmiyor { animation: stk-toast-out .25s ease-in forwards; }
.stk-toast.basari { background: linear-gradient(135deg, var(--stk-yesil), #66BB6A); }
.stk-toast.hata   { background: linear-gradient(135deg, var(--stk-kirmizi), #EF5350); }
.stk-toast.uyari  { background: linear-gradient(135deg, var(--stk-sari), #FFB74D); }
.stk-toast.bilgi  { background: linear-gradient(135deg, #1976D2, #42A5F5); }
.stk-toast i { font-size: 18px; }
@keyframes stk-toast-in  { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
@keyframes stk-toast-out { from { transform: translateX(0); opacity: 1; } to { transform: translateX(120%); opacity: 0; } }

/* === MODAL İÇİ EMPTY STATE === */
.stk-bos-durum { text-align: center; padding: 30px 20px; color: #a0a0b0; }
.stk-bos-durum i { font-size: 42px; opacity: .4; margin-bottom: 10px; display: block; }
.stk-bos-durum p { margin: 0; font-size: 14px; }

/* === LOADING === */
.stk-yukleniyor { display: inline-block; width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.4); border-top-color: #fff; border-radius: 50%; animation: stk-spin .8s linear infinite; }
@keyframes stk-spin { to { transform: rotate(360deg); } }

/* === HAREKET BADGE === */
.stk-hareket-tip {
  display: inline-block; padding: 4px 9px; border-radius: 6px;
  font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px;
}
</style>

<input type="hidden" id="stok_sube_id" value="{{$isletme->id}}">
<div id="stk-toast-konteyner"></div>

<div class="page-header">
  <div class="row">
    <div class="col-md-6">
      <div class="title"><h1>{{$sayfa_baslik}}</h1></div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a></li>
          <li class="breadcrumb-item active">{{$sayfa_baslik}}</li>
        </ol>
      </nav>
    </div>
    <div class="col-md-6 text-right">
      @yetki('urun.tanim_olustur')
      <button class="stk-aksiyon-btn primary" data-toggle="modal" data-target="#urun_modal" onclick="urunModalAc(null)"><i class="fa fa-plus"></i> Yeni Ürün</button>
      @endyetki
      @yetki('urun.sat')
      <button class="stk-aksiyon-btn success" data-toggle="modal" data-target="#hizli_satis_modal" onclick="sepetSifirla()"><i class="fa fa-shopping-cart"></i> Hızlı Satış</button>
      @endyetki
      @yetki('urun.stok_giris')
      <button class="stk-aksiyon-btn warning" data-toggle="modal" data-target="#alis_modal" onclick="alisSifirla()"><i class="fa fa-truck"></i> Alış Girişi</button>
      @endyetki
      @yetki('urun.stok_sayim')
      <button class="stk-aksiyon-btn info" data-toggle="modal" data-target="#sayim_modal" onclick="sayimAc()"><i class="fa fa-clipboard-list"></i> Sayım</button>
      @endyetki
      <div class="dropdown" style="display:inline-block">
        <button class="stk-aksiyon-btn dark dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Ayarlar</button>
        <div class="dropdown-menu dropdown-menu-right" style="border-radius:12px; border:none; box-shadow:0 8px 24px rgba(0,0,0,0.12); padding:6px;">
          <a class="dropdown-item" style="border-radius:8px; padding:9px 12px;" href="#" data-toggle="modal" data-target="#kategori_modal" onclick="kategoriListele()"><i class="fa fa-tags" style="color:var(--stk-mor); width:18px;"></i> Kategoriler</a>
          <a class="dropdown-item" style="border-radius:8px; padding:9px 12px;" href="#" data-toggle="modal" data-target="#depo_modal" onclick="depoListele()"><i class="fa fa-warehouse" style="color:var(--stk-mor); width:18px;"></i> Depolar</a>
          @yetki('urun.tedarikci_yonet')
          <a class="dropdown-item" style="border-radius:8px; padding:9px 12px;" href="#" data-toggle="modal" data-target="#tedarikci_modal" onclick="tedarikciListele()"><i class="fa fa-truck-loading" style="color:var(--stk-mor); width:18px;"></i> Tedarikçiler</a>
          @endyetki
          <a class="dropdown-item" style="border-radius:8px; padding:9px 12px;" href="#" data-toggle="modal" data-target="#transfer_modal" onclick="transferAc()"><i class="fa fa-exchange-alt" style="color:var(--stk-mor); width:18px;"></i> Transfer Yap</a>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-3 col-6 mb-3"><div class="stk-ozet-kart mor"><div style="display:flex;align-items:center;gap:10px;"><span class="stk-ikon-kutu" style="background:var(--stk-mor-soft);color:var(--stk-mor);"><i class="fa fa-cube"></i></span><div><div class="baslik">Toplam Ürün</div><div class="deger" id="ozet_toplam_urun">—</div></div></div></div></div>
  <div class="col-md-3 col-6 mb-3"><div class="stk-ozet-kart sari"><div style="display:flex;align-items:center;gap:10px;"><span class="stk-ikon-kutu" style="background:var(--stk-sari-soft);color:var(--stk-sari);"><i class="fa fa-exclamation-triangle"></i></span><div><div class="baslik">Düşük Stok</div><div class="deger" id="ozet_dusuk">—</div></div></div></div></div>
  <div class="col-md-3 col-6 mb-3"><div class="stk-ozet-kart kirmizi"><div style="display:flex;align-items:center;gap:10px;"><span class="stk-ikon-kutu" style="background:var(--stk-kirmizi-soft);color:var(--stk-kirmizi);"><i class="fa fa-times-circle"></i></span><div><div class="baslik">Tükenen</div><div class="deger" id="ozet_tukenen">—</div></div></div></div></div>
  <div class="col-md-3 col-6 mb-3"><div class="stk-ozet-kart yesil"><div style="display:flex;align-items:center;gap:10px;"><span class="stk-ikon-kutu" style="background:var(--stk-yesil-soft);color:var(--stk-yesil);"><i class="fa fa-wallet"></i></span><div><div class="baslik">Stok Değeri</div><div class="deger" id="ozet_deger">—</div></div></div></div></div>
</div>

<div class="stk-tablo-kart mb-30">
  <div class="stk-tablo-filter">
    <div class="row align-items-center">
      <div class="col-md-5 mb-2"><input type="text" id="urun_ara" class="stk-form-input" placeholder="🔍 Ürün adı, barkod veya SKU ara..."></div>
      <div class="col-md-3 mb-2"><select id="urun_kategori_filtre" class="stk-form-select"><option value="">Tüm Kategoriler</option></select></div>
      <div class="col-md-3 mb-2"><select id="urun_tip_filtre" class="stk-form-select"><option value="">Tüm Tipler</option><option value="satis">Satış</option><option value="sarf">Sarf</option><option value="karma">Karma</option></select></div>
      <div class="col-md-1 mb-2 text-right"><button class="stk-aksiyon-btn outline" onclick="urunleriYukle()" title="Yenile"><i class="fa fa-sync"></i></button></div>
    </div>
  </div>
  <div style="padding:0;">
    <table id="urun_tablosu">
      <thead><tr><th>Ürün</th><th>Kategori</th><th>Tip</th><th>Birim</th><th>Stok</th><th>Alış</th><th>Satış</th><th>Barkod</th><th style="text-align:right;">İşlem</th></tr></thead>
      <tbody></tbody>
    </table>
  </div>
</div>

{{-- ============================================================ --}}
{{-- ÜRÜN EKLE / DÜZENLE MODAL                                    --}}
{{-- ============================================================ --}}
<div class="modal fade stk-modal" id="urun_modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header">
      <h5><i class="fa fa-cube"></i> <span id="urun_modal_baslik">Yeni Ürün</span></h5>
      <button type="button" class="close" data-dismiss="modal" aria-label="Kapat">&times;</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="urun_id" value="0">

      <div class="stk-bolum">
        <div class="stk-bolum-baslik">Temel Bilgiler</div>
        <div class="row">
          <div class="col-md-7 stk-form-grup"><label>Ürün Adı<span class="zorunlu">*</span></label><input type="text" id="f_urun_adi" class="form-control"></div>
          <div class="col-md-5 stk-form-grup">
            <label>Tip</label>
            <div class="stk-segment">
              <button type="button" class="stk-segment-btn aktif" data-tip="satis" onclick="tipSec('satis')">Satış</button>
              <button type="button" class="stk-segment-btn" data-tip="sarf" onclick="tipSec('sarf')">Sarf</button>
              <button type="button" class="stk-segment-btn" data-tip="karma" onclick="tipSec('karma')">Karma</button>
            </div>
            <input type="hidden" id="f_tip" value="satis">
          </div>
          <div class="col-md-6 stk-form-grup"><label>Kategori <span class="opsiyonel">(opsiyonel)</span></label><select id="f_kategori_id" class="form-control"><option value="">— Seç —</option></select></div>
          <div class="col-md-6 stk-form-grup"><label>Tedarikçi <span class="opsiyonel">(opsiyonel)</span></label><select id="f_tedarikci_id" class="form-control"><option value="">— Seç —</option></select></div>
        </div>
      </div>

      <div class="stk-bolum">
        <div class="stk-bolum-baslik">Fiyatlar</div>
        <div class="row">
          <div class="col-md-4 stk-form-grup"><label>Alış Fiyatı (₺)</label><input type="number" step="0.01" id="f_alis_fiyati" class="form-control" placeholder="0.00"></div>
          <div class="col-md-4 stk-form-grup"><label>Satış Fiyatı (₺)<span class="zorunlu">*</span></label><input type="number" step="0.01" id="f_fiyat" class="form-control" placeholder="0.00"></div>
          <div class="col-md-4 stk-form-grup"><label>KDV %</label><input type="number" step="0.01" id="f_kdv" class="form-control" placeholder="0"></div>
        </div>
      </div>

      <div class="stk-bolum">
        <div class="stk-bolum-baslik">Stok</div>
        <div class="row">
          <div class="col-md-6 stk-form-grup"><label>Birim</label>
            <select id="f_birim" class="form-control"><option value="adet">Adet</option><option value="gr">Gram (gr)</option><option value="kg">Kilogram (kg)</option><option value="ml">Mililitre (ml)</option><option value="lt">Litre (lt)</option><option value="paket">Paket</option></select>
          </div>
          <div class="col-md-6 stk-form-grup"><label>Başlangıç Stoğu</label><input type="number" step="0.001" id="f_stok_adedi" class="form-control" value="0"></div>
          <div class="col-md-6 stk-form-grup"><label>Düşük Stok Sınırı <span class="opsiyonel">(sarı uyarı)</span></label><input type="number" step="0.001" id="f_dusuk_stok" class="form-control"></div>
          <div class="col-md-6 stk-form-grup"><label>Kritik Stok Sınırı <span class="opsiyonel">(kırmızı uyarı)</span></label><input type="number" step="0.001" id="f_kritik_stok" class="form-control"></div>
        </div>
      </div>

      <div class="stk-bolum">
        <div class="stk-bolum-baslik">Tanımlayıcı</div>
        <div class="row">
          <div class="col-md-6 stk-form-grup"><label>Barkod</label><input type="text" id="f_barkod" class="form-control"></div>
          <div class="col-md-6 stk-form-grup"><label>SKU <span class="opsiyonel">(opsiyonel)</span></label><input type="text" id="f_sku" class="form-control"></div>
          <div class="col-md-12 stk-form-grup"><label>Açıklama <span class="opsiyonel">(opsiyonel)</span></label><textarea id="f_aciklama" class="form-control" rows="2"></textarea></div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="stk-aksiyon-btn outline" data-dismiss="modal">İptal</button>
      <button class="stk-aksiyon-btn primary" id="urun_kaydet_btn" onclick="urunKaydet()"><i class="fa fa-save"></i> Kaydet</button>
    </div>
  </div></div>
</div>

{{-- ============================================================ --}}
{{-- HIZLI SATIŞ MODAL                                            --}}
{{-- ============================================================ --}}
<div class="modal fade stk-modal" id="hizli_satis_modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header" style="background: linear-gradient(135deg, var(--stk-yesil), #2E7D32);">
      <h5><i class="fa fa-shopping-cart"></i> Hızlı Satış</h5>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
      <div class="stk-bolum">
        <div class="stk-bolum-baslik" style="color:var(--stk-yesil);">Ürün Ekle</div>
        <div class="stk-form-grup">
          <label>Barkod veya ürün adı yazıp <kbd style="background:#fff;border:1px solid #e0e0e7;border-radius:4px;padding:2px 6px;font-size:11px;">Enter</kbd>'a bas</label>
          <input type="text" id="satis_barkod" class="form-control" placeholder="🔍 Barkod gir veya ürün ara..." autocomplete="off">
        </div>
      </div>

      <div class="stk-bolum" style="padding:0;">
        <div id="satis_sepet" style="min-height:140px;"></div>
      </div>

      <div class="sepet-toplam-kutu">
        <span style="font-weight:600;font-size:14px;">Toplam</span>
        <span class="tutar">₺ <span id="satis_toplam">0,00</span></span>
      </div>
    </div>
    <div class="modal-footer">
      <button class="stk-aksiyon-btn outline" data-dismiss="modal">İptal</button>
      <button class="stk-aksiyon-btn success" id="hizli_satis_btn" onclick="hizliSatisGonder()"><i class="fa fa-check"></i> Satışı Tamamla</button>
    </div>
  </div></div>
</div>

{{-- ============================================================ --}}
{{-- ALIŞ GİRİŞİ MODAL                                            --}}
{{-- ============================================================ --}}
<div class="modal fade stk-modal" id="alis_modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header" style="background: linear-gradient(135deg, var(--stk-sari), #EF6C00);">
      <h5><i class="fa fa-truck"></i> Alış Girişi (Mal Kabul)</h5>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
      <div class="stk-bolum">
        <div class="stk-bolum-baslik" style="color:var(--stk-sari);">Belge Bilgileri</div>
        <div class="row">
          <div class="col-md-6 stk-form-grup"><label>Tedarikçi <span class="opsiyonel">(opsiyonel)</span></label><select id="alis_tedarikci_id" class="form-control"><option value="">— Seç —</option></select></div>
          <div class="col-md-6 stk-form-grup"><label>Açıklama / Fiş No <span class="opsiyonel">(opsiyonel)</span></label><input type="text" id="alis_aciklama" class="form-control"></div>
        </div>
      </div>

      <div class="stk-bolum">
        <div class="stk-bolum-baslik" style="color:var(--stk-sari);">Kalemler</div>
        <div id="alis_kalemler" style="min-height:60px;"></div>
        <button type="button" class="stk-aksiyon-btn outline" style="margin-top:10px;" onclick="alisKalemEkle()"><i class="fa fa-plus"></i> Kalem Ekle</button>
      </div>
    </div>
    <div class="modal-footer">
      <button class="stk-aksiyon-btn outline" data-dismiss="modal">İptal</button>
      <button class="stk-aksiyon-btn warning" id="alis_gonder_btn" onclick="alisGonder()"><i class="fa fa-truck-loading"></i> Alışı Kaydet</button>
    </div>
  </div></div>
</div>

{{-- ============================================================ --}}
{{-- SAYIM MODAL                                                  --}}
{{-- ============================================================ --}}
<div class="modal fade stk-modal" id="sayim_modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl"><div class="modal-content">
    <div class="modal-header" style="background: linear-gradient(135deg, #1976D2, #0D47A1);">
      <h5><i class="fa fa-clipboard-list"></i> Sayım Modu</h5>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
      <div class="stk-bolum" style="background: var(--stk-mavi-soft); border-color: #BBDEFB;">
        <div style="display:flex;align-items:flex-start;gap:10px;">
          <i class="fa fa-info-circle" style="color:var(--stk-mavi);margin-top:2px;"></i>
          <div style="color:var(--stk-mavi);font-size:13px;">Her ürünün şu anki sistem stoğunu ve sayılan miktarı görebilirsin. Sadece farklı olanlar için düzeltme hareketi oluşturulur.</div>
        </div>
      </div>
      <div id="sayim_tablo_alani"></div>
    </div>
    <div class="modal-footer">
      <button class="stk-aksiyon-btn outline" data-dismiss="modal">İptal</button>
      <button class="stk-aksiyon-btn info" id="sayim_gonder_btn" onclick="sayimGonder()"><i class="fa fa-check"></i> Sayımı Uygula</button>
    </div>
  </div></div>
</div>

{{-- ============================================================ --}}
{{-- TRANSFER MODAL                                               --}}
{{-- ============================================================ --}}
<div class="modal fade stk-modal" id="transfer_modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <h5><i class="fa fa-exchange-alt"></i> Depolar Arası Transfer</h5>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
      <div class="stk-bolum">
        <div class="stk-form-grup"><label>Ürün<span class="zorunlu">*</span></label><select id="trf_urun_id" class="form-control"></select></div>
        <div class="row">
          <div class="col-md-6 stk-form-grup"><label>Kaynak Depo<span class="zorunlu">*</span></label><select id="trf_kaynak_depo" class="form-control"></select></div>
          <div class="col-md-6 stk-form-grup"><label>Hedef Depo<span class="zorunlu">*</span></label><select id="trf_hedef_depo" class="form-control"></select></div>
        </div>
        <div class="stk-form-grup"><label>Miktar<span class="zorunlu">*</span></label><input type="number" step="0.001" id="trf_miktar" class="form-control" placeholder="0"></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="stk-aksiyon-btn outline" data-dismiss="modal">İptal</button>
      <button class="stk-aksiyon-btn primary" id="trf_gonder_btn" onclick="transferGonder()"><i class="fa fa-exchange-alt"></i> Transfer Yap</button>
    </div>
  </div></div>
</div>

{{-- ============================================================ --}}
{{-- KATEGORİ MODAL                                               --}}
{{-- ============================================================ --}}
<div class="modal fade stk-modal" id="kategori_modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <h5><i class="fa fa-tags"></i> Kategoriler</h5>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
      <div class="stk-bolum">
        <div class="stk-bolum-baslik">Yeni Kategori</div>
        <div class="row align-items-end">
          <div class="col-7 stk-form-grup" style="margin-bottom:0;"><label>Kategori Adı</label><input type="text" id="kategori_yeni_ad" class="form-control" placeholder="örn. Şampuanlar"></div>
          <div class="col-3 stk-form-grup" style="margin-bottom:0;"><label>Renk</label><input type="color" id="kategori_yeni_renk" class="form-control" style="padding:4px; height:38px;" value="#6A1B9A"></div>
          <div class="col-2"><button class="stk-aksiyon-btn primary" style="width:100%;justify-content:center;" onclick="kategoriKaydet()"><i class="fa fa-plus"></i></button></div>
        </div>
      </div>
      <div id="kategori_liste"></div>
    </div>
  </div></div>
</div>

{{-- ============================================================ --}}
{{-- DEPO MODAL                                                   --}}
{{-- ============================================================ --}}
<div class="modal fade stk-modal" id="depo_modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <h5><i class="fa fa-warehouse"></i> Depolar</h5>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
      <div class="stk-bolum">
        <div class="stk-bolum-baslik">Yeni Depo</div>
        <div class="row align-items-end">
          <div class="col-9 stk-form-grup" style="margin-bottom:0;"><label>Depo Adı</label><input type="text" id="depo_yeni_ad" class="form-control" placeholder="örn. Salon Rafı"></div>
          <div class="col-3"><button class="stk-aksiyon-btn primary" style="width:100%;justify-content:center;" onclick="depoKaydet()"><i class="fa fa-plus"></i></button></div>
        </div>
      </div>
      <div id="depo_liste"></div>
    </div>
  </div></div>
</div>

{{-- ============================================================ --}}
{{-- TEDARİKÇİ MODAL                                              --}}
{{-- ============================================================ --}}
<div class="modal fade stk-modal" id="tedarikci_modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <h5><i class="fa fa-truck-loading"></i> Tedarikçiler</h5>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
      <div class="stk-bolum">
        <div class="stk-bolum-baslik">Yeni Tedarikçi</div>
        <div class="row">
          <div class="col-md-6 stk-form-grup"><label>Ad<span class="zorunlu">*</span></label><input type="text" id="stk_ted_ad" class="form-control" placeholder="örn. ABC Kozmetik"></div>
          <div class="col-md-6 stk-form-grup"><label>Telefon</label><input type="text" id="stk_ted_tel" class="form-control" placeholder="0532..."></div>
          <div class="col-md-6 stk-form-grup"><label>Vergi No</label><input type="text" id="stk_ted_vergi" class="form-control"></div>
          <div class="col-md-6 stk-form-grup"><label>E-posta</label><input type="email" id="stk_ted_email" class="form-control"></div>
          <div class="col-md-12 stk-form-grup"><label>Adres</label><textarea id="stk_ted_adres" class="form-control" rows="2"></textarea></div>
        </div>
        <button class="stk-aksiyon-btn primary" id="ted_kaydet_btn" onclick="tedarikciKaydet()" style="width:100%; justify-content:center;"><i class="fa fa-plus"></i> Tedarikçi Ekle</button>
      </div>
      <div id="tedarikci_liste"></div>
    </div>
  </div></div>
</div>

{{-- ============================================================ --}}
{{-- HAREKET TARİHÇESİ MODAL                                      --}}
{{-- ============================================================ --}}
<div class="modal fade stk-modal" id="hareket_modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header">
      <h5><i class="fa fa-history"></i> <span id="hareket_modal_baslik">Stok Hareketleri</span></h5>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
      <div id="hareket_liste_alani"></div>
    </div>
  </div></div>
</div>

<script>
const STOK_BASE = '/isletmeyonetim/stok/';
const STOK_CSRF = '{{ csrf_token() }}';
let urunCache = [];
let kategoriCache = [];
let depoCache = [];
let tedarikciCache = [];
let satisSepeti = [];
var alisKalemleri = [];

// ============================================================
// TOAST BİLDİRİM SİSTEMİ
// ============================================================
function toast(mesaj, tip) {
    tip = tip || 'basari';
    var ikonlar = { basari: 'check-circle', hata: 'exclamation-circle', uyari: 'exclamation-triangle', bilgi: 'info-circle' };
    var t = document.createElement('div');
    t.className = 'stk-toast ' + tip;
    t.innerHTML = '<i class="fa fa-' + ikonlar[tip] + '"></i><span>' + mesaj + '</span>';
    document.getElementById('stk-toast-konteyner').appendChild(t);
    setTimeout(function(){
        t.classList.add('gosterilmiyor');
        setTimeout(function(){ t.remove(); }, 250);
    }, 3500);
}

// ============================================================
// API HELPER
// ============================================================
async function stokApi(action, data, method) {
    method = method || 'POST';
    data = data || {};
    const opts = {
        method: method,
        headers: { 'X-CSRF-TOKEN': STOK_CSRF, 'Accept': 'application/json' },
        credentials: 'same-origin'
    };
    if (method === 'POST') {
        const fd = new FormData();
        for (const k in data) {
            const v = data[k];
            if (v === null || v === undefined) continue;
            if (typeof v === 'object') fd.append(k, JSON.stringify(v));
            else fd.append(k, v);
        }
        opts.body = fd;
    }
    try {
        const r = await fetch(STOK_BASE + action, opts);
        const txt = await r.text();
        if (!r.ok) {
            // 422 ise body içinde mesaj olabilir, JSON parse dene
            try { const j = JSON.parse(txt); toast(j.mesaj || ('Sunucu hatası: ' + r.status), 'hata'); }
            catch(e) { toast('İşlem başarısız (HTTP ' + r.status + ')', 'hata'); console.error('Stok API '+r.status+':', txt); }
            return null;
        }
        if (!txt) return null;
        try { return JSON.parse(txt); }
        catch(e) { console.error('Geçersiz JSON yanıt:', txt); toast('Sunucudan geçersiz yanıt geldi', 'hata'); return null; }
    } catch (e) {
        console.error('Ağ hatası:', e);
        toast('Bağlantı hatası: ' + e.message, 'hata');
        return null;
    }
}

// ============================================================
// FORMAT YARDIMCILARI
// ============================================================
function tlFormat(n){ return Number(n||0).toLocaleString('tr-TR', {minimumFractionDigits:2, maximumFractionDigits:2}); }
function adetFormat(n){ var x = Number(n||0); return Number.isInteger(x) ? x.toString() : x.toFixed(3).replace(/\.?0+$/,''); }
function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]); }); }
function escapeJs(s){ return String(s||'').replace(/'/g, "\\'").replace(/"/g, '&quot;'); }
function stokRozetSinif(stok, dusuk, kritik){
    var s = Number(stok||0), d = Number(dusuk||0), k = Number(kritik||0);
    if (s <= 0) return 'kirmizi';
    if (k > 0 && s <= k) return 'kirmizi';
    if (d > 0 && s <= d) return 'sari';
    return 'yesil';
}

// ============================================================
// VERİ YÜKLEYİCİLER
// ============================================================
async function ozetiYukle(){
    const o = await stokApi('ozet', {}, 'GET');
    if (!o) return;
    document.getElementById('ozet_toplam_urun').textContent = o.toplam_urun || 0;
    document.getElementById('ozet_dusuk').textContent       = o.dusuk_stok  || 0;
    document.getElementById('ozet_tukenen').textContent     = o.tukenen     || 0;
    document.getElementById('ozet_deger').textContent       = '₺' + tlFormat(o.toplam_satis_degeri || 0);
}

async function kategorileriYukle(){
    kategoriCache = await stokApi('kategoriler', {}, 'GET') || [];
    const sel = document.getElementById('urun_kategori_filtre');
    const fsel = document.getElementById('f_kategori_id');
    sel.innerHTML  = '<option value="">Tüm Kategoriler</option>';
    fsel.innerHTML = '<option value="">— Seç —</option>';
    kategoriCache.forEach(function(k){
        sel.innerHTML  += '<option value="'+k.id+'">'+escapeHtml(k.ad)+'</option>';
        fsel.innerHTML += '<option value="'+k.id+'">'+escapeHtml(k.ad)+'</option>';
    });
}

async function depolariYukle(){
    depoCache = await stokApi('depolar', {}, 'GET') || [];
    const k = document.getElementById('trf_kaynak_depo'), h = document.getElementById('trf_hedef_depo');
    if (k){
        k.innerHTML = ''; h.innerHTML = '';
        depoCache.forEach(function(d){
            k.innerHTML += '<option value="'+d.id+'">'+escapeHtml(d.depo_adi)+'</option>';
            h.innerHTML += '<option value="'+d.id+'">'+escapeHtml(d.depo_adi)+'</option>';
        });
    }
}

async function tedarikcileriYukle(){
    tedarikciCache = await stokApi('tedarikciler', {}, 'GET') || [];
    const sel  = document.getElementById('f_tedarikci_id');
    const asel = document.getElementById('alis_tedarikci_id');
    var html = '<option value="">— Seç —</option>' + tedarikciCache.map(function(t){return '<option value="'+t.id+'">'+escapeHtml(t.ad)+'</option>';}).join('');
    if (sel)  sel.innerHTML  = html;
    if (asel) asel.innerHTML = html;
}

async function urunleriYukle(){
    const arama  = document.getElementById('urun_ara').value;
    const kat    = document.getElementById('urun_kategori_filtre').value;
    const tip    = document.getElementById('urun_tip_filtre').value;
    urunCache = await stokApi('urunler', { arama: arama, kategori_id: kat, tip: tip }) || [];
    const tbody = document.querySelector('#urun_tablosu tbody');
    tbody.innerHTML = urunCache.map(function(u){
        var stokSinif = stokRozetSinif(u.stok_adedi, u.dusuk_stok_siniri, u.kritik_stok_siniri);
        var tipChip = '<span class="stk-tip-chip '+(u.tip||'satis')+'">'+(u.tip || 'satis').toUpperCase()+'</span>';
        var katAdi = u.kategori_adi ? ('<span style="color:'+(u.kategori_renk||'#999')+';">●</span> '+escapeHtml(u.kategori_adi)) : '<span class="text-muted">—</span>';
        var adJs  = escapeJs(u.urun_adi);
        return '<tr>'
            + '<td><strong>'+escapeHtml(u.urun_adi)+'</strong>'+(u.aciklama ? '<br><small class="text-muted">'+escapeHtml(u.aciklama)+'</small>' : '')+'</td>'
            + '<td>'+katAdi+'</td>'
            + '<td>'+tipChip+'</td>'
            + '<td>'+escapeHtml(u.birim||'adet')+'</td>'
            + '<td><span class="stk-rozet '+stokSinif+'">'+adetFormat(u.stok_adedi)+' '+escapeHtml(u.birim||'')+'</span></td>'
            + '<td>'+(u.alis_fiyati ? '₺'+tlFormat(u.alis_fiyati) : '<span class="text-muted">—</span>')+'</td>'
            + '<td><strong style="color:var(--stk-mor);">₺'+tlFormat(u.fiyat)+'</strong></td>'
            + '<td>'+(u.barkod ? '<code style="background:#f5f5f7;padding:2px 6px;border-radius:4px;font-size:11px;">'+escapeHtml(u.barkod)+'</code>' : '<span class="text-muted">—</span>')+'</td>'
            + '<td style="text-align:right;white-space:nowrap;">'
                + '<button class="urun-aksiyon-btn duz" data-toggle="modal" data-target="#urun_modal" onclick="urunModalAc(\''+u.id+'\')" title="Düzenle"><i class="fa fa-edit"></i></button>'
                + '<button class="urun-aksiyon-btn gec" onclick="hareketleriGoster(\''+u.id+'\',\''+adJs+'\')" title="Hareketler"><i class="fa fa-history"></i></button>'
                + '<button class="urun-aksiyon-btn sil" onclick="urunSil(\''+u.id+'\',\''+adJs+'\')" title="Sil"><i class="fa fa-trash"></i></button>'
            + '</td>'
        + '</tr>';
    }).join('') || '<tr><td colspan="9" style="text-align:center;padding:50px;color:#a0a0b0;"><i class="fa fa-inbox" style="font-size:48px;opacity:.3;display:block;margin-bottom:10px;"></i>Henüz ürün yok</td></tr>';

    const trfSel = document.getElementById('trf_urun_id');
    if (trfSel) trfSel.innerHTML = urunCache.map(function(u){return '<option value="'+u.id+'">'+escapeHtml(u.urun_adi)+'</option>';}).join('');
}

// ============================================================
// ÜRÜN EKLE / DÜZENLE
// ============================================================
function tipSec(tip){
    document.getElementById('f_tip').value = tip;
    document.querySelectorAll('#urun_modal .stk-segment-btn').forEach(function(b){
        b.classList.toggle('aktif', b.dataset.tip === tip);
    });
}

function urunModalAc(id){
    document.getElementById('urun_modal_baslik').textContent = id ? 'Ürün Düzenle' : 'Yeni Ürün';
    document.getElementById('urun_id').value = id || '0';
    const u = id ? urunCache.find(function(x){return x.id == id;}) : null;
    document.getElementById('f_urun_adi').value      = u ? u.urun_adi  : '';
    tipSec(u ? (u.tip || 'satis') : 'satis');
    document.getElementById('f_kategori_id').value   = u ? (u.kategori_id || '') : '';
    document.getElementById('f_tedarikci_id').value  = u ? (u.tedarikci_id || '') : '';
    document.getElementById('f_alis_fiyati').value   = u ? (u.alis_fiyati || '') : '';
    document.getElementById('f_fiyat').value         = u ? (u.fiyat || '') : '';
    document.getElementById('f_kdv').value           = u ? (u.kdv_orani || '') : '';
    document.getElementById('f_birim').value         = u ? (u.birim || 'adet') : 'adet';
    document.getElementById('f_barkod').value        = u ? (u.barkod || '') : '';
    document.getElementById('f_sku').value           = u ? (u.sku || '') : '';
    document.getElementById('f_stok_adedi').value    = u ? (u.stok_adedi || 0) : 0;
    document.getElementById('f_dusuk_stok').value    = u ? (u.dusuk_stok_siniri || '') : '';
    document.getElementById('f_kritik_stok').value   = u ? (u.kritik_stok_siniri || '') : '';
    document.getElementById('f_aciklama').value      = u ? (u.aciklama || '') : '';
}

async function urunKaydet(){
    var data = {
        id: document.getElementById('urun_id').value,
        urun_adi: document.getElementById('f_urun_adi').value.trim(),
        tip: document.getElementById('f_tip').value,
        kategori_id: document.getElementById('f_kategori_id').value,
        tedarikci_id: document.getElementById('f_tedarikci_id').value,
        alis_fiyati: document.getElementById('f_alis_fiyati').value,
        fiyat: document.getElementById('f_fiyat').value,
        kdv_orani: document.getElementById('f_kdv').value,
        birim: document.getElementById('f_birim').value,
        barkod: document.getElementById('f_barkod').value.trim(),
        sku: document.getElementById('f_sku').value.trim(),
        stok_adedi: document.getElementById('f_stok_adedi').value,
        dusuk_stok_siniri: document.getElementById('f_dusuk_stok').value,
        kritik_stok_siniri: document.getElementById('f_kritik_stok').value,
        aciklama: document.getElementById('f_aciklama').value,
        kullanici_tipi: 'isletme_yonetim'
    };
    if (!data.urun_adi)       { toast('Ürün adı zorunlu', 'uyari'); return; }
    if (!data.fiyat)          { toast('Satış fiyatı zorunlu', 'uyari'); return; }

    var btn = document.getElementById('urun_kaydet_btn');
    btn.disabled = true; var eski = btn.innerHTML; btn.innerHTML = '<span class="stk-yukleniyor"></span> Kaydediliyor...';
    const r = await stokApi('urun-kaydet', data);
    btn.disabled = false; btn.innerHTML = eski;

    if (r && r.id) {
        toast(data.id == '0' ? 'Ürün eklendi' : 'Ürün güncellendi', 'basari');
        $('#urun_modal').modal('hide');
        urunleriYukle(); ozetiYukle();
    }
}

async function urunSil(id, ad){
    if (!confirm(ad + ' silinsin mi? (Pasif olarak işaretlenir)')) return;
    const r = await stokApi('urun-sil', { urun_id: id });
    if (r) { toast('Ürün silindi', 'basari'); urunleriYukle(); ozetiYukle(); }
}

// ============================================================
// HAREKETLER
// ============================================================
async function hareketleriGoster(urunId, urunAdi){
    document.getElementById('hareket_modal_baslik').textContent = urunAdi + ' — Hareketler';
    document.getElementById('hareket_liste_alani').innerHTML = '<div class="text-center" style="padding:30px;"><div class="stk-yukleniyor" style="border-color:#e0e0e7;border-top-color:var(--stk-mor);width:24px;height:24px;"></div></div>';
    $('#hareket_modal').modal('show');
    const list = await stokApi('hareketler', { urun_id: urunId, limit: 200 }) || [];
    if (list.length === 0) {
        document.getElementById('hareket_liste_alani').innerHTML = '<div class="stk-bos-durum"><i class="fa fa-inbox"></i><p>Henüz hareket yok</p></div>';
        return;
    }
    var tipRenkleri = { alis:'#43A047', satis:'#1565C0', sarf:'#AD1457', fire:'#E53935', sayim:'#F6A609', acilis:'#6A1B9A', iade:'#43A047', transfer_giris:'#0288D1', transfer_cikis:'#FF7043', manuel:'#455A64' };
    var html = '<table style="width:100%;"><thead><tr style="border-bottom:2px solid var(--stk-cizgi);"><th style="padding:10px;font-size:11px;color:#8a8a9a;text-transform:uppercase;letter-spacing:0.5px;">Tarih</th><th style="padding:10px;font-size:11px;color:#8a8a9a;text-transform:uppercase;letter-spacing:0.5px;">Tip</th><th style="padding:10px;font-size:11px;color:#8a8a9a;text-transform:uppercase;letter-spacing:0.5px;text-align:right;">Miktar</th><th style="padding:10px;font-size:11px;color:#8a8a9a;text-transform:uppercase;letter-spacing:0.5px;">Açıklama</th></tr></thead><tbody>';
    html += list.map(function(h){
        var renk = Number(h.miktar) > 0 ? 'var(--stk-yesil)' : 'var(--stk-kirmizi)';
        var tipRenk = tipRenkleri[h.hareket_tipi] || '#455A64';
        var tipBg = tipRenk + '20';
        return '<tr style="border-bottom:1px solid var(--stk-cizgi);"><td style="padding:12px 10px;font-size:12px;color:#455A64;">'+(h.tarih||'')+'</td>'
            + '<td style="padding:12px 10px;"><span class="stk-hareket-tip" style="background:'+tipBg+';color:'+tipRenk+';">'+escapeHtml(h.hareket_tipi)+'</span></td>'
            + '<td style="padding:12px 10px;text-align:right;color:'+renk+';font-weight:800;">'+(Number(h.miktar)>0?'+':'')+adetFormat(h.miktar)+'</td>'
            + '<td style="padding:12px 10px;font-size:12px;color:#455A64;">'+escapeHtml(h.aciklama||'')+'</td></tr>';
    }).join('');
    html += '</tbody></table>';
    document.getElementById('hareket_liste_alani').innerHTML = html;
}

// ============================================================
// HIZLI SATIŞ
// ============================================================
function sepetSifirla(){ satisSepeti = []; sepetCiz(); document.getElementById('satis_barkod').value = ''; }

function sepeteEkle(urun){
    const v = satisSepeti.find(function(x){return x.urun_id == urun.id;});
    if (v) v.miktar += 1;
    else satisSepeti.push({ urun_id: urun.id, urun_adi: urun.urun_adi, birim_fiyat: Number(urun.fiyat||0), miktar: 1, birim: urun.birim || 'adet' });
    sepetCiz();
}
function sepetCiz(){
    var html = '';
    if (satisSepeti.length === 0) {
        html = '<div class="stk-bos-durum"><i class="fa fa-shopping-basket"></i><p>Sepet boş — barkod tara veya ürün ara</p></div>';
    } else {
        html = satisSepeti.map(function(k,i){
            return '<div class="sepet-satir">'
                + '<div style="flex:1;"><div style="font-weight:700;font-size:14px;">'+escapeHtml(k.urun_adi)+'</div><div style="color:#8a8a9a;font-size:11px;">₺'+tlFormat(k.birim_fiyat)+' / '+escapeHtml(k.birim)+'</div></div>'
                + '<input type="number" min="0.001" step="0.001" class="miktar-input" value="'+k.miktar+'" onchange="satisSepeti['+i+'].miktar=Number(this.value); sepetCiz()">'
                + '<div style="text-align:right;min-width:90px;"><strong style="color:var(--stk-mor);font-size:15px;">₺'+tlFormat(k.birim_fiyat*k.miktar)+'</strong></div>'
                + '<button class="urun-aksiyon-btn sil" onclick="satisSepeti.splice('+i+',1); sepetCiz()"><i class="fa fa-times"></i></button>'
                + '</div>';
        }).join('');
    }
    document.getElementById('satis_sepet').innerHTML = html;
    document.getElementById('satis_toplam').textContent = tlFormat(satisSepeti.reduce(function(s,k){return s + k.birim_fiyat*k.miktar;}, 0));
}
async function hizliSatisGonder(){
    if (!satisSepeti.length) { toast('Sepet boş', 'uyari'); return; }
    var btn = document.getElementById('hizli_satis_btn');
    btn.disabled = true; var eski = btn.innerHTML; btn.innerHTML = '<span class="stk-yukleniyor"></span> İşleniyor...';
    const r = await stokApi('hizli-satis', {
        sepet: satisSepeti.map(function(k){return { urun_id: k.urun_id, miktar: k.miktar, birim_fiyat: k.birim_fiyat };}),
        kullanici_tipi: 'isletme_yonetim'
    });
    btn.disabled = false; btn.innerHTML = eski;
    if (r && r.status === 'ok'){
        toast('Satış tamam — ₺' + tlFormat(r.toplam_tutar), 'basari');
        satisSepeti = []; sepetCiz();
        $('#hizli_satis_modal').modal('hide');
        urunleriYukle(); ozetiYukle();
    }
}

// ============================================================
// ALIŞ GİRİŞİ
// ============================================================
function alisSifirla(){
    alisKalemleri = [{ urun_id: '', miktar: 1, birim_alis_fiyati: 0 }];
    document.getElementById('alis_tedarikci_id').value = '';
    document.getElementById('alis_aciklama').value = '';
    alisCiz();
}
function alisKalemEkle(){
    alisKalemleri.push({ urun_id: '', miktar: 1, birim_alis_fiyati: 0 });
    alisCiz();
}
function alisCiz(){
    if (alisKalemleri.length === 0) {
        document.getElementById('alis_kalemler').innerHTML = '<div class="stk-bos-durum"><i class="fa fa-list"></i><p>Kalem ekle</p></div>';
        return;
    }
    var opts = '<option value="">— Ürün seç —</option>' + urunCache.map(function(u){return '<option value="'+u.id+'">'+escapeHtml(u.urun_adi)+'</option>';}).join('');
    document.getElementById('alis_kalemler').innerHTML = alisKalemleri.map(function(k,i){
        var sec = '<select class="form-control form-control-sm" onchange="alisKalemleri['+i+'].urun_id=this.value">' + opts.replace('value="'+k.urun_id+'"', 'value="'+k.urun_id+'" selected') + '</select>';
        return '<div class="row align-items-end" style="margin-bottom:8px;">'
            + '<div class="col-md-6 stk-form-grup" style="margin-bottom:0;"><label style="font-size:10px;">Ürün</label>'+sec+'</div>'
            + '<div class="col-md-3 stk-form-grup" style="margin-bottom:0;"><label style="font-size:10px;">Miktar</label><input type="number" step="0.001" class="form-control form-control-sm" value="'+k.miktar+'" onchange="alisKalemleri['+i+'].miktar=Number(this.value)"></div>'
            + '<div class="col-md-2 stk-form-grup" style="margin-bottom:0;"><label style="font-size:10px;">Birim ₺</label><input type="number" step="0.01" class="form-control form-control-sm" value="'+k.birim_alis_fiyati+'" onchange="alisKalemleri['+i+'].birim_alis_fiyati=Number(this.value)"></div>'
            + '<div class="col-md-1"><button class="urun-aksiyon-btn sil" onclick="alisKalemleri.splice('+i+',1); alisCiz()"><i class="fa fa-times"></i></button></div>'
            + '</div>';
    }).join('');
}
async function alisGonder(){
    var dolu = alisKalemleri.filter(function(k){return k.urun_id && Number(k.miktar) > 0;});
    if (!dolu.length) { toast('En az 1 kalem ekle (ürün seç + miktar gir)', 'uyari'); return; }
    var btn = document.getElementById('alis_gonder_btn');
    btn.disabled = true; var eski = btn.innerHTML; btn.innerHTML = '<span class="stk-yukleniyor"></span> Kaydediliyor...';
    const r = await stokApi('alis-girisi', {
        tedarikci_id: document.getElementById('alis_tedarikci_id').value,
        aciklama: document.getElementById('alis_aciklama').value,
        kalemler: dolu,
        kullanici_tipi: 'isletme_yonetim'
    });
    btn.disabled = false; btn.innerHTML = eski;
    if (r && r.status === 'ok'){ toast('Alış kaydedildi (' + r.kalem_sayisi + ' kalem)', 'basari'); alisSifirla(); $('#alis_modal').modal('hide'); urunleriYukle(); ozetiYukle(); }
}

// ============================================================
// SAYIM
// ============================================================
function sayimAc(){
    if (depoCache.length === 0) { document.getElementById('sayim_tablo_alani').innerHTML = '<div class="stk-bos-durum"><i class="fa fa-warehouse"></i><p>Önce bir depo tanımla</p></div>'; return; }
    if (urunCache.length === 0) { document.getElementById('sayim_tablo_alani').innerHTML = '<div class="stk-bos-durum"><i class="fa fa-inbox"></i><p>Önce ürün ekle</p></div>'; return; }
    var opts = depoCache.map(function(d){return '<option value="'+d.id+'"'+(d.varsayilan === '1' ? ' selected' : '')+'>'+escapeHtml(d.depo_adi)+'</option>';}).join('');
    var rows = urunCache.map(function(u){
        return '<tr style="border-bottom:1px solid var(--stk-cizgi);"><td style="padding:10px;"><strong>'+escapeHtml(u.urun_adi)+'</strong></td>'
            + '<td style="padding:10px;color:#8a8a9a;font-size:13px;">'+adetFormat(u.stok_adedi)+' '+escapeHtml(u.birim||'')+'</td>'
            + '<td style="padding:10px;text-align:right;"><input type="number" step="0.001" data-urun="'+u.id+'" class="form-control form-control-sm sayim-input" style="width:100px;display:inline-block;text-align:center;" value="'+u.stok_adedi+'"></td></tr>';
    }).join('');
    document.getElementById('sayim_tablo_alani').innerHTML =
        '<div class="stk-bolum"><div class="stk-form-grup" style="margin-bottom:0;"><label>Hangi Depo Sayılıyor?</label><select id="sayim_depo_id" class="form-control" style="max-width:320px;">'+opts+'</select></div></div>'
        + '<div class="stk-bolum" style="padding:0;"><table style="width:100%;"><thead><tr style="border-bottom:2px solid var(--stk-cizgi);"><th style="padding:10px;font-size:11px;color:#8a8a9a;text-transform:uppercase;">Ürün</th><th style="padding:10px;font-size:11px;color:#8a8a9a;text-transform:uppercase;">Sistem Stoğu</th><th style="padding:10px;font-size:11px;color:#8a8a9a;text-transform:uppercase;text-align:right;">Sayılan Miktar</th></tr></thead><tbody>'+rows+'</tbody></table></div>';
}
async function sayimGonder(){
    var depoSec = document.getElementById('sayim_depo_id');
    if (!depoSec) { toast('Önce sayımı aç', 'uyari'); return; }
    const depoId = depoSec.value;
    var inputs = document.querySelectorAll('.sayim-input');
    var kalemler = [];
    inputs.forEach(function(i){ kalemler.push({ urun_id: i.dataset.urun, depo_id: depoId, sayilan_miktar: Number(i.value||0) }); });
    var btn = document.getElementById('sayim_gonder_btn');
    btn.disabled = true; var eski = btn.innerHTML; btn.innerHTML = '<span class="stk-yukleniyor"></span> Uygulanıyor...';
    const r = await stokApi('sayim', { kalemler: kalemler, kullanici_tipi: 'isletme_yonetim' });
    btn.disabled = false; btn.innerHTML = eski;
    if (r && r.status === 'ok'){ toast(r.sayilan_kalem + ' kalemde düzeltme uygulandı', 'basari'); $('#sayim_modal').modal('hide'); urunleriYukle(); ozetiYukle(); }
}

// ============================================================
// TRANSFER
// ============================================================
function transferAc(){
    document.getElementById('trf_miktar').value = '';
    if (depoCache.length < 2) toast('Transfer için en az 2 depo gerekli', 'uyari');
}
async function transferGonder(){
    var miktar = Number(document.getElementById('trf_miktar').value || 0);
    if (miktar <= 0) { toast('Miktar girin', 'uyari'); return; }
    var btn = document.getElementById('trf_gonder_btn');
    btn.disabled = true; var eski = btn.innerHTML; btn.innerHTML = '<span class="stk-yukleniyor"></span> Aktarılıyor...';
    const r = await stokApi('transfer', {
        urun_id: document.getElementById('trf_urun_id').value,
        kaynak_depo_id: document.getElementById('trf_kaynak_depo').value,
        hedef_depo_id: document.getElementById('trf_hedef_depo').value,
        miktar: miktar,
        kullanici_tipi: 'isletme_yonetim'
    });
    btn.disabled = false; btn.innerHTML = eski;
    if (r && r.status === 'ok'){ toast('Transfer tamam', 'basari'); $('#transfer_modal').modal('hide'); urunleriYukle(); depolariYukle(); }
}

// ============================================================
// KATEGORİ / DEPO / TEDARİKÇİ YÖNETİMİ
// ============================================================
async function kategoriListele(){
    await kategorileriYukle();
    var html = kategoriCache.length === 0
        ? '<div class="stk-bos-durum"><i class="fa fa-tags"></i><p>Henüz kategori yok</p></div>'
        : kategoriCache.map(function(k){
            return '<div class="stk-satir-kart">'
                + '<span class="stk-satir-ikon-kutu" style="background:'+(k.renk||'#6A1B9A')+'20;color:'+(k.renk||'#6A1B9A')+';"><i class="fa fa-tag"></i></span>'
                + '<div style="flex:1;"><div class="stk-satir-baslik">'+escapeHtml(k.ad)+'</div></div>'
                + '<button class="stk-satir-mini-btn sil" onclick="kategoriSil(\''+k.id+'\')"><i class="fa fa-trash"></i></button>'
                + '</div>';
        }).join('');
    document.getElementById('kategori_liste').innerHTML = html;
}
async function kategoriKaydet(){
    var ad = document.getElementById('kategori_yeni_ad').value.trim();
    if (!ad) { toast('Kategori adı girin', 'uyari'); return; }
    const r = await stokApi('kategori-kaydet', { ad: ad, renk: document.getElementById('kategori_yeni_renk').value });
    if (r && r.id) {
        toast('Kategori eklendi', 'basari');
        document.getElementById('kategori_yeni_ad').value = '';
        kategoriListele();
    }
}
async function kategoriSil(id){
    if (!confirm('Kategoriyi sil?')) return;
    const r = await stokApi('kategori-sil', { id: id });
    if (r) { toast('Silindi', 'basari'); kategoriListele(); }
}

async function depoListele(){
    await depolariYukle();
    var html = depoCache.length === 0
        ? '<div class="stk-bos-durum"><i class="fa fa-warehouse"></i><p>Henüz depo yok</p></div>'
        : depoCache.map(function(d){
            var varBadge = d.varsayilan === '1' ? '<span style="background:var(--stk-yesil-soft);color:#2E7D32;font-size:10px;padding:2px 8px;border-radius:5px;font-weight:700;margin-left:6px;">VARSAYILAN</span>' : '';
            return '<div class="stk-satir-kart">'
                + '<span class="stk-satir-ikon-kutu" style="background:var(--stk-mor-soft);color:var(--stk-mor);"><i class="fa fa-warehouse"></i></span>'
                + '<div style="flex:1;"><div class="stk-satir-baslik">'+escapeHtml(d.depo_adi)+varBadge+'</div><div class="stk-satir-alt">Toplam: '+adetFormat(d.toplam_stok)+'</div></div>'
                + (d.varsayilan === '1' ? '' : '<button class="stk-satir-mini-btn sil" onclick="depoSil(\''+d.id+'\')"><i class="fa fa-trash"></i></button>')
                + '</div>';
        }).join('');
    document.getElementById('depo_liste').innerHTML = html;
}
async function depoKaydet(){
    var ad = document.getElementById('depo_yeni_ad').value.trim();
    if (!ad) { toast('Depo adı girin', 'uyari'); return; }
    const r = await stokApi('depo-kaydet', { depo_adi: ad });
    if (r && r.id) {
        toast('Depo eklendi', 'basari');
        document.getElementById('depo_yeni_ad').value = '';
        depoListele();
    }
}
async function depoSil(id){
    if (!confirm('Depoyu sil?')) return;
    await stokApi('depo-sil', { id: id });
    depoListele();
}

async function tedarikciListele(){
    await tedarikcileriYukle();
    var html = tedarikciCache.length === 0
        ? '<div class="stk-bos-durum"><i class="fa fa-truck-loading"></i><p>Henüz tedarikçi yok</p></div>'
        : tedarikciCache.map(function(t){
            return '<div class="stk-satir-kart">'
                + '<span class="stk-satir-ikon-kutu" style="background:var(--stk-sari-soft);color:var(--stk-sari);"><i class="fa fa-truck-loading"></i></span>'
                + '<div style="flex:1;"><div class="stk-satir-baslik">'+escapeHtml(t.ad)+'</div>'
                + ((t.telefon||t.email) ? '<div class="stk-satir-alt">'+escapeHtml(t.telefon||'')+(t.email ? ' • '+escapeHtml(t.email) : '')+'</div>' : '')
                + '</div>'
                + '<button class="stk-satir-mini-btn sil" onclick="tedarikciSil(\''+t.id+'\')"><i class="fa fa-trash"></i></button>'
                + '</div>';
        }).join('');
    document.getElementById('tedarikci_liste').innerHTML = html;
}
async function tedarikciKaydet(){
    var ad = document.getElementById('stk_ted_ad').value.trim();
    if (!ad) { toast('Tedarikçi adı zorunlu', 'uyari'); return; }
    var btn = document.getElementById('ted_kaydet_btn');
    btn.disabled = true; var eski = btn.innerHTML; btn.innerHTML = '<span class="stk-yukleniyor"></span> Ekleniyor...';
    const r = await stokApi('tedarikci-kaydet', {
        ad: ad,
        telefon: document.getElementById('stk_ted_tel').value,
        vergi_no: document.getElementById('stk_ted_vergi').value,
        email: document.getElementById('stk_ted_email').value,
        adres: document.getElementById('stk_ted_adres').value
    });
    btn.disabled = false; btn.innerHTML = eski;
    if (r && r.id) {
        toast('Tedarikçi eklendi', 'basari');
        ['stk_ted_ad','stk_ted_tel','stk_ted_vergi','stk_ted_email','stk_ted_adres'].forEach(function(id){ document.getElementById(id).value = ''; });
        tedarikciListele();
    }
}
async function tedarikciSil(id){
    if (!confirm('Tedarikçiyi sil?')) return;
    await stokApi('tedarikci-sil', { id: id });
    tedarikciListele();
}

// ============================================================
// HIZLI SATIŞ — BARKOD ENTER
// ============================================================
document.addEventListener('DOMContentLoaded', function(){
    const barkodInput = document.getElementById('satis_barkod');
    if (barkodInput){
        barkodInput.addEventListener('keypress', async function(e){
            if (e.key !== 'Enter') return;
            e.preventDefault();
            const kod = barkodInput.value.trim();
            if (!kod) return;
            var bulundu = false;
            try {
                const r = await stokApi('urun-barkod', { barkod: kod });
                if (r && r.id) { sepeteEkle(r); bulundu = true; }
            } catch(e) {}
            if (!bulundu) {
                const u = urunCache.find(function(x){
                    return ((x.urun_adi||'').toLowerCase().indexOf(kod.toLowerCase()) >= 0) || x.barkod === kod;
                });
                if (u) { sepeteEkle(u); bulundu = true; }
            }
            if (!bulundu) toast('Bulunamadı: ' + kod, 'uyari');
            barkodInput.value = ''; barkodInput.focus();
        });
    }

    document.getElementById('urun_ara').addEventListener('input', function(){ clearTimeout(window._stkAramaT); window._stkAramaT = setTimeout(urunleriYukle, 350); });
    document.getElementById('urun_kategori_filtre').addEventListener('change', urunleriYukle);
    document.getElementById('urun_tip_filtre').addEventListener('change', urunleriYukle);
    ozetiYukle();
    kategorileriYukle();
    depolariYukle();
    tedarikcileriYukle();
    urunleriYukle();
});
</script>

@endsection()
