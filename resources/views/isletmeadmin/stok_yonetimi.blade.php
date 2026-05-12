@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

<style>
.stok-ozet-kart { background: #fff; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f5; }
.stok-ozet-kart .baslik { font-size: 12px; color: #8a8a9a; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
.stok-ozet-kart .deger   { font-size: 26px; font-weight: 700; margin-top: 4px; }
.stok-ozet-kart.mor    { border-top: 4px solid #6A1B9A; }
.stok-ozet-kart.sari   { border-top: 4px solid #F6A609; }
.stok-ozet-kart.kirmizi{ border-top: 4px solid #E53935; }
.stok-ozet-kart.yesil  { border-top: 4px solid #43A047; }
.stok-rozet { display:inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
.stok-rozet.yesil  { background: #E8F5E9; color: #2E7D32; }
.stok-rozet.sari   { background: #FFF8E1; color: #B27300; }
.stok-rozet.kirmizi{ background: #FFEBEE; color: #C62828; }
.stok-tip-chip { font-size: 11px; padding: 2px 8px; border-radius: 4px; background: #ECEFF1; color: #455A64; display:inline-block; }
.stok-tip-chip.satis { background:#E3F2FD; color:#1565C0; }
.stok-tip-chip.sarf  { background:#FCE4EC; color:#AD1457; }
.stok-tip-chip.karma { background:#F3E5F5; color:#6A1B9A; }
.stok-arac-btn { margin-right: 6px; margin-bottom: 6px; }
.stok-modal .form-label { font-weight: 600; font-size: 13px; margin-bottom: 4px; }
#urun_tablosu tbody tr td { vertical-align: middle; }
.urun-aksiyon-btn { padding: 4px 10px; font-size: 12px; margin: 1px; }
.sepet-satir { display:flex; align-items:center; gap:8px; padding:8px; border-bottom:1px solid #eee; }
.sepet-satir .miktar-input { width: 70px; }
</style>

<input type="hidden" id="stok_sube_id" value="{{$isletme->id}}">

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
      <button class="btn btn-primary stok-arac-btn" data-toggle="modal" data-target="#urun_modal" onclick="urunModalAc(null)"><i class="fa fa-plus"></i> Yeni Ürün</button>
      <button class="btn btn-success stok-arac-btn" data-toggle="modal" data-target="#hizli_satis_modal"><i class="fa fa-shopping-cart"></i> Hızlı Satış</button>
      <button class="btn btn-warning stok-arac-btn" data-toggle="modal" data-target="#alis_modal"><i class="fa fa-truck"></i> Alış Girişi</button>
      <button class="btn btn-info stok-arac-btn" data-toggle="modal" data-target="#sayim_modal" onclick="sayimAc()"><i class="fa fa-clipboard-list"></i> Sayım</button>
      <div class="dropdown" style="display:inline-block">
        <button class="btn btn-secondary stok-arac-btn dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Ayarlar</button>
        <div class="dropdown-menu dropdown-menu-right">
          <a class="dropdown-item" href="#" data-toggle="modal" data-target="#kategori_modal" onclick="kategoriListele()">Kategoriler</a>
          <a class="dropdown-item" href="#" data-toggle="modal" data-target="#depo_modal" onclick="depoListele()">Depolar</a>
          <a class="dropdown-item" href="#" data-toggle="modal" data-target="#tedarikci_modal" onclick="tedarikciListele()">Tedarikçiler</a>
          <a class="dropdown-item" href="#" data-toggle="modal" data-target="#transfer_modal">Transfer Yap</a>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-3 col-6 mb-3"><div class="stok-ozet-kart mor">     <div class="baslik">Toplam Ürün</div><div class="deger" id="ozet_toplam_urun">—</div></div></div>
  <div class="col-md-3 col-6 mb-3"><div class="stok-ozet-kart sari">    <div class="baslik">Düşük Stok</div><div class="deger" id="ozet_dusuk">—</div></div></div>
  <div class="col-md-3 col-6 mb-3"><div class="stok-ozet-kart kirmizi"> <div class="baslik">Tükenen</div><div class="deger" id="ozet_tukenen">—</div></div></div>
  <div class="col-md-3 col-6 mb-3"><div class="stok-ozet-kart yesil">   <div class="baslik">Stok Değeri (₺)</div><div class="deger" id="ozet_deger">—</div></div></div>
</div>

<div class="card-box mb-30">
  <div class="row" style="padding:14px 18px 0 18px">
    <div class="col-md-5"><input type="text" id="urun_ara" class="form-control" placeholder="🔍 Ürün adı, barkod veya SKU ara..."></div>
    <div class="col-md-3"><select id="urun_kategori_filtre" class="form-control"><option value="">Tüm Kategoriler</option></select></div>
    <div class="col-md-3"><select id="urun_tip_filtre" class="form-control"><option value="">Tüm Tipler</option><option value="satis">Satış</option><option value="sarf">Sarf</option><option value="karma">Karma</option></select></div>
    <div class="col-md-1 text-right"><button class="btn btn-light" onclick="urunleriYukle()"><i class="fa fa-sync"></i></button></div>
  </div>
  <div style="padding:18px">
    <table class="table table-hover" id="urun_tablosu">
      <thead><tr><th>Ürün</th><th>Kategori</th><th>Tip</th><th>Birim</th><th>Stok</th><th>Alış</th><th>Satış</th><th>Barkod</th><th>İşlem</th></tr></thead>
      <tbody></tbody>
    </table>
  </div>
</div>

{{-- URUN EKLE / DUZENLE --}}
<div class="modal fade stok-modal" id="urun_modal" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title" id="urun_modal_baslik">Yeni Ürün</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
      <input type="hidden" id="urun_id" value="0">
      <div class="row">
        <div class="col-md-7"><div class="form-group"><label class="form-label">Ürün Adı *</label><input type="text" id="f_urun_adi" class="form-control"></div></div>
        <div class="col-md-5"><div class="form-group"><label class="form-label">Tip</label>
          <select id="f_tip" class="form-control"><option value="satis">Satış</option><option value="sarf">Sarf</option><option value="karma">Karma (Satış+Sarf)</option></select></div></div>
        <div class="col-md-6"><div class="form-group"><label class="form-label">Kategori</label><select id="f_kategori_id" class="form-control"><option value="">— Seç —</option></select></div></div>
        <div class="col-md-6"><div class="form-group"><label class="form-label">Tedarikçi</label><select id="f_tedarikci_id" class="form-control"><option value="">— Seç —</option></select></div></div>
        <div class="col-md-3"><div class="form-group"><label class="form-label">Alış Fiyatı (₺)</label><input type="number" step="0.01" id="f_alis_fiyati" class="form-control"></div></div>
        <div class="col-md-3"><div class="form-group"><label class="form-label">Satış Fiyatı (₺) *</label><input type="number" step="0.01" id="f_fiyat" class="form-control"></div></div>
        <div class="col-md-3"><div class="form-group"><label class="form-label">KDV %</label><input type="number" step="0.01" id="f_kdv" class="form-control"></div></div>
        <div class="col-md-3"><div class="form-group"><label class="form-label">Birim</label>
          <select id="f_birim" class="form-control"><option value="adet">Adet</option><option value="gr">gr</option><option value="kg">kg</option><option value="ml">ml</option><option value="lt">lt</option><option value="paket">Paket</option></select></div></div>
        <div class="col-md-4"><div class="form-group"><label class="form-label">Barkod</label><input type="text" id="f_barkod" class="form-control"></div></div>
        <div class="col-md-4"><div class="form-group"><label class="form-label">SKU</label><input type="text" id="f_sku" class="form-control"></div></div>
        <div class="col-md-4"><div class="form-group"><label class="form-label">Başlangıç Stoğu</label><input type="number" step="0.001" id="f_stok_adedi" class="form-control" value="0"></div></div>
        <div class="col-md-6"><div class="form-group"><label class="form-label">Düşük Stok Sınırı</label><input type="number" step="0.001" id="f_dusuk_stok" class="form-control"></div></div>
        <div class="col-md-6"><div class="form-group"><label class="form-label">Kritik Stok Sınırı</label><input type="number" step="0.001" id="f_kritik_stok" class="form-control"></div></div>
        <div class="col-md-12"><div class="form-group"><label class="form-label">Açıklama</label><textarea id="f_aciklama" class="form-control" rows="2"></textarea></div></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-danger" data-dismiss="modal">İptal</button>
      <button class="btn btn-success" onclick="urunKaydet()"><i class="fa fa-save"></i> Kaydet</button>
    </div>
  </div></div>
</div>

{{-- HIZLI SATIS --}}
<div class="modal fade stok-modal" id="hizli_satis_modal" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5>Hızlı Satış</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
      <div class="form-group"><input type="text" id="satis_barkod" class="form-control" placeholder="Barkod gir veya ürün ara, Enter'a bas..."></div>
      <div id="satis_sepet" style="border:1px solid #eee; border-radius:6px; min-height:120px; padding:8px"></div>
      <div class="text-right mt-3" style="font-size:18px"><strong>Toplam: ₺ <span id="satis_toplam">0.00</span></strong></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-danger" data-dismiss="modal">İptal</button>
      <button class="btn btn-success" onclick="hizliSatisGonder()"><i class="fa fa-check"></i> Satışı Tamamla</button>
    </div>
  </div></div>
</div>

{{-- ALIS GIRISI --}}
<div class="modal fade stok-modal" id="alis_modal" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5>Alış Girişi (Mal Kabul)</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
      <div class="row">
        <div class="col-md-6"><div class="form-group"><label class="form-label">Tedarikçi</label><select id="alis_tedarikci_id" class="form-control"><option value="">— Seç —</option></select></div></div>
        <div class="col-md-6"><div class="form-group"><label class="form-label">Açıklama / Fiş No</label><input type="text" id="alis_aciklama" class="form-control"></div></div>
      </div>
      <div id="alis_kalemler" style="border:1px solid #eee; border-radius:6px; padding:8px; min-height:80px"></div>
      <button class="btn btn-sm btn-light mt-2" onclick="alisKalemEkle()"><i class="fa fa-plus"></i> Kalem Ekle</button>
    </div>
    <div class="modal-footer">
      <button class="btn btn-danger" data-dismiss="modal">İptal</button>
      <button class="btn btn-success" onclick="alisGonder()"><i class="fa fa-truck-loading"></i> Alışı Kaydet</button>
    </div>
  </div></div>
</div>

{{-- SAYIM --}}
<div class="modal fade stok-modal" id="sayim_modal" tabindex="-1">
  <div class="modal-dialog modal-xl"><div class="modal-content">
    <div class="modal-header"><h5>Sayım Modu</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
      <p class="text-muted">Aşağıda her ürünün şu anki stoğunu ve sayılan miktarı görebilirsin. Sadece farklı olanlar düzeltme hareketi olarak kaydedilir.</p>
      <div id="sayim_tablo_alani"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-danger" data-dismiss="modal">İptal</button>
      <button class="btn btn-success" onclick="sayimGonder()"><i class="fa fa-check"></i> Sayımı Uygula</button>
    </div>
  </div></div>
</div>

{{-- TRANSFER --}}
<div class="modal fade stok-modal" id="transfer_modal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5>Depolar Arası Transfer</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
      <div class="form-group"><label class="form-label">Ürün</label><select id="trf_urun_id" class="form-control"></select></div>
      <div class="form-group"><label class="form-label">Kaynak Depo</label><select id="trf_kaynak_depo" class="form-control"></select></div>
      <div class="form-group"><label class="form-label">Hedef Depo</label><select id="trf_hedef_depo" class="form-control"></select></div>
      <div class="form-group"><label class="form-label">Miktar</label><input type="number" step="0.001" id="trf_miktar" class="form-control"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-danger" data-dismiss="modal">İptal</button>
      <button class="btn btn-success" onclick="transferGonder()">Transfer Yap</button>
    </div>
  </div></div>
</div>

{{-- KATEGORI YONETIMI --}}
<div class="modal fade stok-modal" id="kategori_modal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5>Kategoriler</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
      <div class="row mb-3">
        <div class="col-7"><input type="text" id="kategori_yeni_ad" class="form-control" placeholder="Yeni kategori adı"></div>
        <div class="col-3"><input type="color" id="kategori_yeni_renk" class="form-control" value="#6A1B9A"></div>
        <div class="col-2"><button class="btn btn-success" onclick="kategoriKaydet()">Ekle</button></div>
      </div>
      <table class="table"><tbody id="kategori_liste"></tbody></table>
    </div>
  </div></div>
</div>

{{-- DEPO YONETIMI --}}
<div class="modal fade stok-modal" id="depo_modal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5>Depolar</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
      <div class="row mb-3">
        <div class="col-9"><input type="text" id="depo_yeni_ad" class="form-control" placeholder="Yeni depo adı"></div>
        <div class="col-3"><button class="btn btn-success" onclick="depoKaydet()">Ekle</button></div>
      </div>
      <table class="table"><tbody id="depo_liste"></tbody></table>
    </div>
  </div></div>
</div>

{{-- TEDARIKCI YONETIMI --}}
<div class="modal fade stok-modal" id="tedarikci_modal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5>Tedarikçiler</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
      <div class="row mb-3">
        <div class="col-5"><input type="text" id="ted_yeni_ad" class="form-control" placeholder="Tedarikçi adı"></div>
        <div class="col-4"><input type="text" id="ted_yeni_tel" class="form-control" placeholder="Telefon"></div>
        <div class="col-3"><button class="btn btn-success" onclick="tedarikciKaydet()">Ekle</button></div>
      </div>
      <table class="table"><tbody id="tedarikci_liste"></tbody></table>
    </div>
  </div></div>
</div>

{{-- HAREKET TARIHCESI --}}
<div class="modal fade stok-modal" id="hareket_modal" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 id="hareket_modal_baslik">Stok Hareketleri</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body"><table class="table table-sm"><thead><tr><th>Tarih</th><th>Tip</th><th>Miktar</th><th>Açıklama</th></tr></thead><tbody id="hareket_liste"></tbody></table></div>
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

function stokApi(action, data, method) {
    method = method || 'POST';
    data = data || {};
    const opts = {
        method,
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
    return fetch(STOK_BASE + action, opts).then(function(r){return r.json();}).catch(function(e){ console.error(e); return null; });
}

function tlFormat(n){ return Number(n||0).toLocaleString('tr-TR', {minimumFractionDigits:2, maximumFractionDigits:2}); }
function adetFormat(n){ var x = Number(n||0); return Number.isInteger(x) ? x.toString() : x.toFixed(3); }

function stokRozetSinif(stok, dusuk, kritik){
    var s = Number(stok||0), d = Number(dusuk||0), k = Number(kritik||0);
    if (s <= 0) return 'kirmizi';
    if (k > 0 && s <= k) return 'kirmizi';
    if (d > 0 && s <= d) return 'sari';
    return 'yesil';
}

async function ozetiYukle(){
    const o = await stokApi('ozet', {}, 'GET');
    if (!o) return;
    document.getElementById('ozet_toplam_urun').textContent = o.toplam_urun || 0;
    document.getElementById('ozet_dusuk').textContent       = o.dusuk_stok  || 0;
    document.getElementById('ozet_tukenen').textContent     = o.tukenen     || 0;
    document.getElementById('ozet_deger').textContent       = tlFormat(o.toplam_satis_degeri || 0);
}

async function kategorileriYukle(){
    kategoriCache = await stokApi('kategoriler', {}, 'GET') || [];
    const sel = document.getElementById('urun_kategori_filtre');
    const fsel = document.getElementById('f_kategori_id');
    sel.innerHTML = '<option value="">Tüm Kategoriler</option>';
    fsel.innerHTML = '<option value="">— Seç —</option>';
    kategoriCache.forEach(function(k){
        sel.innerHTML  += '<option value="'+k.id+'">'+k.ad+'</option>';
        fsel.innerHTML += '<option value="'+k.id+'">'+k.ad+'</option>';
    });
}

async function depolariYukle(){
    depoCache = await stokApi('depolar', {}, 'GET') || [];
    const k = document.getElementById('trf_kaynak_depo'), h = document.getElementById('trf_hedef_depo');
    if (k){
        k.innerHTML = ''; h.innerHTML = '';
        depoCache.forEach(function(d){
            k.innerHTML += '<option value="'+d.id+'">'+d.depo_adi+'</option>';
            h.innerHTML += '<option value="'+d.id+'">'+d.depo_adi+'</option>';
        });
    }
}

async function tedarikcileriYukle(){
    tedarikciCache = await stokApi('tedarikciler', {}, 'GET') || [];
    const sel  = document.getElementById('f_tedarikci_id');
    const asel = document.getElementById('alis_tedarikci_id');
    var html = '<option value="">— Seç —</option>' + tedarikciCache.map(function(t){return '<option value="'+t.id+'">'+t.ad+'</option>';}).join('');
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
        var tipChip = '<span class="stok-tip-chip '+(u.tip||'satis')+'">'+(u.tip || 'satis')+'</span>';
        var katAdi = u.kategori_adi ? ('<small style="color:'+(u.kategori_renk||'#999')+'">●</small> '+u.kategori_adi) : '<span class="text-muted">—</span>';
        var adSafe = (u.urun_adi||'').replace(/'/g,"");
        return '<tr>'
            + '<td><strong>'+u.urun_adi+'</strong>'+(u.aciklama ? '<br><small class="text-muted">'+u.aciklama+'</small>' : '')+'</td>'
            + '<td>'+katAdi+'</td>'
            + '<td>'+tipChip+'</td>'
            + '<td>'+(u.birim||'adet')+'</td>'
            + '<td><span class="stok-rozet '+stokSinif+'">'+adetFormat(u.stok_adedi)+' '+(u.birim||'')+'</span></td>'
            + '<td>'+(u.alis_fiyati ? '₺'+tlFormat(u.alis_fiyati) : '<span class="text-muted">—</span>')+'</td>'
            + '<td>₺'+tlFormat(u.fiyat)+'</td>'
            + '<td>'+(u.barkod || '<span class="text-muted">—</span>')+'</td>'
            + '<td>'
                + '<button class="btn btn-sm btn-outline-primary urun-aksiyon-btn" onclick="urunModalAc(\''+u.id+'\')"><i class="fa fa-edit"></i></button>'
                + '<button class="btn btn-sm btn-outline-info urun-aksiyon-btn"    onclick="hareketleriGoster(\''+u.id+'\',\''+adSafe+'\')"><i class="fa fa-history"></i></button>'
                + '<button class="btn btn-sm btn-outline-danger urun-aksiyon-btn"   onclick="urunSil(\''+u.id+'\',\''+adSafe+'\')"><i class="fa fa-trash"></i></button>'
            + '</td>'
        + '</tr>';
    }).join('') || '<tr><td colspan="9" class="text-center text-muted py-4">Henüz ürün yok</td></tr>';

    const trfSel = document.getElementById('trf_urun_id');
    if (trfSel) trfSel.innerHTML = urunCache.map(function(u){return '<option value="'+u.id+'">'+u.urun_adi+'</option>';}).join('');
}

function urunModalAc(id){
    document.getElementById('urun_modal_baslik').textContent = id ? 'Ürün Düzenle' : 'Yeni Ürün';
    document.getElementById('urun_id').value = id || '0';
    const u = id ? urunCache.find(function(x){return x.id == id;}) : null;
    document.getElementById('f_urun_adi').value      = u ? u.urun_adi  : '';
    document.getElementById('f_tip').value           = u ? (u.tip||'satis') : 'satis';
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
        urun_adi: document.getElementById('f_urun_adi').value,
        tip: document.getElementById('f_tip').value,
        kategori_id: document.getElementById('f_kategori_id').value,
        tedarikci_id: document.getElementById('f_tedarikci_id').value,
        alis_fiyati: document.getElementById('f_alis_fiyati').value,
        fiyat: document.getElementById('f_fiyat').value,
        kdv_orani: document.getElementById('f_kdv').value,
        birim: document.getElementById('f_birim').value,
        barkod: document.getElementById('f_barkod').value,
        sku: document.getElementById('f_sku').value,
        stok_adedi: document.getElementById('f_stok_adedi').value,
        dusuk_stok_siniri: document.getElementById('f_dusuk_stok').value,
        kritik_stok_siniri: document.getElementById('f_kritik_stok').value,
        aciklama: document.getElementById('f_aciklama').value,
        kullanici_tipi: 'isletme_yonetim'
    };
    if (!data.urun_adi || !data.fiyat) { alert('Ürün adı ve satış fiyatı zorunlu.'); return; }
    const r = await stokApi('urun-kaydet', data);
    if (r && r.id) {
        $('#urun_modal').modal('hide');
        urunleriYukle(); ozetiYukle();
    } else alert('Kaydedilemedi');
}

async function urunSil(id, ad){
    if (!confirm(ad + ' silinsin mi? (Pasif olarak işaretlenir)')) return;
    await stokApi('urun-sil', { urun_id: id });
    urunleriYukle(); ozetiYukle();
}

async function hareketleriGoster(urunId, urunAdi){
    document.getElementById('hareket_modal_baslik').textContent = urunAdi + ' — Hareketler';
    const list = await stokApi('hareketler', { urun_id: urunId, limit: 200 }) || [];
    document.getElementById('hareket_liste').innerHTML = list.map(function(h){
        var renk = Number(h.miktar) > 0 ? '#2E7D32' : '#C62828';
        return '<tr><td>'+(h.tarih||'')+'</td><td><span class="stok-tip-chip">'+h.hareket_tipi+'</span></td><td style="color:'+renk+';font-weight:bold">'+(Number(h.miktar)>0?'+':'')+adetFormat(h.miktar)+'</td><td>'+(h.aciklama||'')+'</td></tr>';
    }).join('') || '<tr><td colspan="4" class="text-center text-muted">Hareket yok</td></tr>';
    $('#hareket_modal').modal('show');
}

/* HIZLI SATIS */
document.addEventListener('DOMContentLoaded', function(){
    const barkodInput = document.getElementById('satis_barkod');
    if (barkodInput){
        barkodInput.addEventListener('keypress', async function(e){
            if (e.key !== 'Enter') return;
            e.preventDefault();
            const kod = barkodInput.value.trim();
            if (!kod) return;
            const r = await stokApi('urun-barkod', { barkod: kod });
            if (r && r.id) sepeteEkle(r);
            else {
                const u = urunCache.find(function(x){
                    return ((x.urun_adi||'').toLowerCase().indexOf(kod.toLowerCase()) >= 0) || x.barkod === kod;
                });
                if (u) sepeteEkle(u);
                else alert('Bulunamadı: ' + kod);
            }
            barkodInput.value = ''; barkodInput.focus();
        });
    }
});

function sepeteEkle(urun){
    const v = satisSepeti.find(function(x){return x.urun_id == urun.id;});
    if (v) v.miktar += 1;
    else satisSepeti.push({ urun_id: urun.id, urun_adi: urun.urun_adi, birim_fiyat: Number(urun.fiyat||0), miktar: 1 });
    sepetCiz();
}
function sepetCiz(){
    document.getElementById('satis_sepet').innerHTML = satisSepeti.map(function(k,i){
        return '<div class="sepet-satir">'
            + '<strong style="flex:1">'+k.urun_adi+'</strong>'
            + '<input type="number" min="0.001" step="0.001" class="form-control form-control-sm miktar-input" value="'+k.miktar+'" onchange="satisSepeti['+i+'].miktar=Number(this.value); sepetCiz()">'
            + '<span style="width:60px;text-align:right">₺'+tlFormat(k.birim_fiyat)+'</span>'
            + '<span style="width:80px;text-align:right"><strong>₺'+tlFormat(k.birim_fiyat*k.miktar)+'</strong></span>'
            + '<button class="btn btn-sm btn-outline-danger" onclick="satisSepeti.splice('+i+',1); sepetCiz()"><i class="fa fa-times"></i></button>'
            + '</div>';
    }).join('');
    document.getElementById('satis_toplam').textContent = tlFormat(satisSepeti.reduce(function(s,k){return s + k.birim_fiyat*k.miktar;}, 0));
}
async function hizliSatisGonder(){
    if (!satisSepeti.length) { alert('Sepet boş'); return; }
    const r = await stokApi('hizli-satis', {
        sepet: satisSepeti.map(function(k){return { urun_id: k.urun_id, miktar: k.miktar, birim_fiyat: k.birim_fiyat };}),
        kullanici_tipi: 'isletme_yonetim'
    });
    if (r && r.status === 'ok'){
        alert('Satış tamamlandı. Toplam: ₺' + tlFormat(r.toplam_tutar));
        satisSepeti = []; sepetCiz();
        $('#hizli_satis_modal').modal('hide');
        urunleriYukle(); ozetiYukle();
    } else alert('Hata');
}

/* ALIS GIRISI */
var alisKalemleri = [];
function alisKalemEkle(){
    alisKalemleri.push({ urun_id: '', miktar: 1, birim_alis_fiyati: 0 });
    alisCiz();
}
function alisCiz(){
    var opts = urunCache.map(function(u){return '<option value="'+u.id+'">'+u.urun_adi+'</option>';}).join('');
    document.getElementById('alis_kalemler').innerHTML = alisKalemleri.map(function(k,i){
        return '<div class="row mb-2">'
            + '<div class="col-md-6"><select class="form-control form-control-sm" onchange="alisKalemleri['+i+'].urun_id=this.value">'+opts+'</select></div>'
            + '<div class="col-md-3"><input type="number" step="0.001" class="form-control form-control-sm" placeholder="Miktar" value="'+k.miktar+'" onchange="alisKalemleri['+i+'].miktar=this.value"></div>'
            + '<div class="col-md-2"><input type="number" step="0.01"  class="form-control form-control-sm" placeholder="Birim ₺" value="'+k.birim_alis_fiyati+'" onchange="alisKalemleri['+i+'].birim_alis_fiyati=this.value"></div>'
            + '<div class="col-md-1"><button class="btn btn-sm btn-outline-danger" onclick="alisKalemleri.splice('+i+',1); alisCiz()"><i class="fa fa-times"></i></button></div>'
            + '</div>';
    }).join('');
}
async function alisGonder(){
    if (!alisKalemleri.length) { alert('Kalem ekle'); return; }
    const r = await stokApi('alis-girisi', {
        tedarikci_id: document.getElementById('alis_tedarikci_id').value,
        aciklama: document.getElementById('alis_aciklama').value,
        kalemler: alisKalemleri,
        kullanici_tipi: 'isletme_yonetim'
    });
    if (r && r.status === 'ok'){ alert('Alış kaydedildi ('+r.kalem_sayisi+' kalem)'); alisKalemleri=[]; alisCiz(); $('#alis_modal').modal('hide'); urunleriYukle(); ozetiYukle(); }
    else alert('Hata');
}

/* SAYIM */
function sayimAc(){
    var opts = depoCache.map(function(d){return '<option value="'+d.id+'">'+d.depo_adi+'</option>';}).join('');
    var rows = urunCache.map(function(u){return '<tr><td>'+u.urun_adi+'</td><td>'+adetFormat(u.stok_adedi)+' '+(u.birim||'')+'</td><td><input type="number" step="0.001" data-urun="'+u.id+'" class="form-control form-control-sm sayim-input" value="'+u.stok_adedi+'"></td></tr>';}).join('');
    document.getElementById('sayim_tablo_alani').innerHTML =
        '<div class="form-group"><label>Hangi Depo Sayılıyor?</label><select id="sayim_depo_id" class="form-control" style="max-width:300px">'+opts+'</select></div>'
        + '<table class="table table-sm"><thead><tr><th>Ürün</th><th>Sistem Stoğu</th><th>Sayılan Miktar</th></tr></thead><tbody>'+rows+'</tbody></table>';
}
async function sayimGonder(){
    const depoId = document.getElementById('sayim_depo_id').value;
    var inputs = document.querySelectorAll('.sayim-input');
    var kalemler = [];
    inputs.forEach(function(i){ kalemler.push({ urun_id: i.dataset.urun, depo_id: depoId, sayilan_miktar: Number(i.value||0) }); });
    const r = await stokApi('sayim', { kalemler: kalemler, kullanici_tipi: 'isletme_yonetim' });
    if (r && r.status === 'ok'){ alert(r.sayilan_kalem + ' kalemde fark bulundu, düzeltme hareketi oluşturuldu.'); $('#sayim_modal').modal('hide'); urunleriYukle(); ozetiYukle(); }
    else alert('Hata');
}

/* TRANSFER */
async function transferGonder(){
    const r = await stokApi('transfer', {
        urun_id: document.getElementById('trf_urun_id').value,
        kaynak_depo_id: document.getElementById('trf_kaynak_depo').value,
        hedef_depo_id: document.getElementById('trf_hedef_depo').value,
        miktar: document.getElementById('trf_miktar').value,
        kullanici_tipi: 'isletme_yonetim'
    });
    if (r && r.status === 'ok'){ alert('Transfer tamamlandı'); $('#transfer_modal').modal('hide'); urunleriYukle(); }
    else alert(r && r.mesaj || 'Hata');
}

/* KATEGORI / DEPO / TEDARIKCI YONETIMI */
async function kategoriListele(){
    await kategorileriYukle();
    document.getElementById('kategori_liste').innerHTML = kategoriCache.map(function(k){
        return '<tr><td><span style="color:'+(k.renk||'#999')+'">●</span> '+k.ad+'</td><td class="text-right"><button class="btn btn-sm btn-outline-danger" onclick="kategoriSil(\''+k.id+'\')"><i class="fa fa-trash"></i></button></td></tr>';
    }).join('') || '<tr><td colspan="2" class="text-center text-muted">Kategori yok</td></tr>';
}
async function kategoriKaydet(){
    var ad = document.getElementById('kategori_yeni_ad').value.trim();
    if (!ad) return;
    await stokApi('kategori-kaydet', { ad: ad, renk: document.getElementById('kategori_yeni_renk').value });
    document.getElementById('kategori_yeni_ad').value = '';
    kategoriListele();
}
async function kategoriSil(id){ if (!confirm('Silinsin mi?')) return; await stokApi('kategori-sil', { id: id }); kategoriListele(); }

async function depoListele(){
    await depolariYukle();
    document.getElementById('depo_liste').innerHTML = depoCache.map(function(d){
        return '<tr><td>'+d.depo_adi+' '+(d.varsayilan==='1' ? '<small class="text-success">(varsayılan)</small>' : '')+'<br><small class="text-muted">Toplam: '+adetFormat(d.toplam_stok)+'</small></td><td class="text-right"><button class="btn btn-sm btn-outline-danger" onclick="depoSil(\''+d.id+'\')"><i class="fa fa-trash"></i></button></td></tr>';
    }).join('');
}
async function depoKaydet(){
    var ad = document.getElementById('depo_yeni_ad').value.trim();
    if (!ad) return;
    await stokApi('depo-kaydet', { depo_adi: ad });
    document.getElementById('depo_yeni_ad').value = '';
    depoListele();
}
async function depoSil(id){
    if (!confirm('Silinsin mi?')) return;
    const r = await stokApi('depo-sil', { id: id });
    if (r && r.mesaj) alert(r.mesaj);
    depoListele();
}

async function tedarikciListele(){
    await tedarikcileriYukle();
    document.getElementById('tedarikci_liste').innerHTML = tedarikciCache.map(function(t){
        return '<tr><td>'+t.ad+'<br><small class="text-muted">'+(t.telefon||'')+'</small></td><td class="text-right"><button class="btn btn-sm btn-outline-danger" onclick="tedarikciSil(\''+t.id+'\')"><i class="fa fa-trash"></i></button></td></tr>';
    }).join('');
}
async function tedarikciKaydet(){
    var ad = document.getElementById('ted_yeni_ad').value.trim();
    if (!ad) return;
    await stokApi('tedarikci-kaydet', { ad: ad, telefon: document.getElementById('ted_yeni_tel').value });
    document.getElementById('ted_yeni_ad').value = ''; document.getElementById('ted_yeni_tel').value = '';
    tedarikciListele();
}
async function tedarikciSil(id){ if (!confirm('Silinsin mi?')) return; await stokApi('tedarikci-sil', { id: id }); tedarikciListele(); }

/* ARAMA */
var aramaZamanlayici = null;
document.addEventListener('DOMContentLoaded', function(){
    document.getElementById('urun_ara').addEventListener('input', function(){ clearTimeout(aramaZamanlayici); aramaZamanlayici = setTimeout(urunleriYukle, 350); });
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
