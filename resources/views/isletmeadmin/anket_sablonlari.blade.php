@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

<style>
   .akt-card { background:#fff; border-radius:10px; box-shadow:0 2px 8px rgba(92,0,142,.06); }
   .akt-h { background:#5C008E; color:#fff; padding:18px 22px; border-radius:10px 10px 0 0; }
   .akt-h h1 { margin:0; font-size:20px; font-weight:700; color:#fff; }
   .akt-h .alt { font-size:13px; opacity:.92; margin-top:3px; }

   .sablon-tablo th { background:#faf5ff; color:#3a1a52; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; padding:10px 14px; border:none; }
   .sablon-tablo td { font-size:13.5px; padding:12px 14px; vertical-align:middle; border-top:1px solid #ece6f3; }
   .sablon-tablo tr:hover td { background:#fbfafd; }
   .badge-aktif { background:#10b981; color:#fff; padding:3px 9px; border-radius:11px; font-size:11px; font-weight:600; }
   .badge-pasif { background:#94a3b8; color:#fff; padding:3px 9px; border-radius:11px; font-size:11px; font-weight:600; }
   .badge-vars  { background:#5C008E; color:#fff; padding:3px 9px; border-radius:11px; font-size:11px; font-weight:600; margin-left:5px; }

   .btn-mor { background:#5C008E; color:#fff; border:none; padding:9px 18px; border-radius:7px; font-size:13.5px; font-weight:600; }
   .btn-mor:hover { background:#48006e; color:#fff; }
   .btn-mor-out { background:transparent; color:#5C008E; border:1.5px solid #5C008E; padding:7px 14px; border-radius:7px; font-size:12.5px; font-weight:600; }
   .btn-mor-out:hover { background:#5C008E; color:#fff; }

   /* Modal — kompakt, marka moru, viewport ortası */
   #anketSablonModal { z-index:10550; }
   #anketSablonModal .modal-dialog { max-width:760px; margin:auto; }
   #anketSablonModal .modal-content { border-radius:14px; border:none; box-shadow:0 18px 50px rgba(92,0,142,.18); }
   #anketSablonModal .modal-header { background:#faf5ff; border-bottom:1px solid #ece6f3; padding:14px 22px; border-radius:14px 14px 0 0; }
   #anketSablonModal .modal-header h4 { color:#3a1a52; font-size:17px; font-weight:700; margin:0; display:flex; align-items:center; gap:10px; }
   #anketSablonModal .modal-header .ikon-kutu { width:34px; height:34px; background:#5C008E; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#fff; }
   #anketSablonModal .modal-body { padding:18px 22px; max-height:78vh; overflow-y:auto; }
   #anketSablonModal .modal-footer { padding:12px 22px; border-top:1px solid #ece6f3; }

   #anketSablonModal label { font-size:12.5px; color:#5C008E; font-weight:700; text-transform:uppercase; letter-spacing:.3px; margin-bottom:5px; }
   #anketSablonModal .form-control { border:1.5px solid #dfd6ea; border-radius:7px; font-size:13.5px; padding:8px 11px; min-height:36px; }
   #anketSablonModal .form-control:focus { border-color:#5C008E; box-shadow:0 0 0 3px rgba(92,0,142,.1); }

   .ekle-btn-grup { background:#fbfafd; border:1px solid #ece6f3; border-radius:8px; padding:11px; }
   .ekle-btn-grup .baslik { font-size:11px; color:#5C008E; font-weight:700; text-transform:uppercase; letter-spacing:.4px; margin-bottom:7px; }
   .ekle-btn-grup .btn { border-radius:6px; font-size:12px; padding:5px 11px; margin:2px; font-weight:600; }
   .ekle-btn-grup .btn i { margin-right:4px; }

   .soru-satiri { background:#fff; border:1px solid #ece6f3; border-radius:8px; padding:10px 12px; margin-bottom:8px; border-left-width:4px; }
   .soru-satiri .tip-badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.3px; }

   .istatistik-kartlar { display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px; margin-bottom:18px; }
   .istat-kart { background:#fff; border-radius:10px; padding:14px 18px; border:1px solid #ece6f3; }
   .istat-kart .et { font-size:11px; color:#8a8295; font-weight:600; text-transform:uppercase; letter-spacing:.4px; }
   .istat-kart .deg { font-size:24px; font-weight:800; color:#3a1a52; margin-top:4px; }
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
         <a href="/isletmeyonetim/anket-sonuclari?sube={{$isletme->id}}" class="btn-mor-out" style="margin-right:6px; display:inline-block; text-decoration:none;">
            <i class="fa fa-bar-chart"></i> Sonuçları Gör
         </a>
         <button type="button" onclick="yeniSablonAc()" class="btn-mor">
            <i class="fa fa-plus"></i> Yeni Anket Şablonu
         </button>
      </div>
   </div>
</div>

<div class="akt-card mb-30" style="padding:0; overflow:hidden;">
   <div style="padding:18px 22px;">
      @if(count($sablonlar) === 0)
         <div style="text-align:center; padding:50px 20px;">
            <div style="font-size:50px; color:#d8d2e0;"><i class="fa fa-comments-o"></i></div>
            <h4 style="color:#5C008E; margin-top:14px;">Henüz Anket Şablonu Yok</h4>
            <p style="color:#8a8295; max-width:480px; margin:8px auto 16px; font-size:13.5px;">
               Müşterilerinizden geri bildirim almak için bir anket şablonu oluşturun. NPS skoru, yıldızlı memnuniyet ve açık uçlu sorularla deneyimlerini ölçümleyin.
            </p>
            <button onclick="yeniSablonAc()" class="btn-mor"><i class="fa fa-plus"></i> İlk Şablonu Oluştur</button>
            <button onclick="ornekSablonOlustur()" class="btn-mor-out" style="margin-left:6px;">
               <i class="fa fa-magic"></i> Hazır Şablonu Yükle
            </button>
         </div>
      @else
         <table class="table sablon-tablo" style="margin:0;">
            <thead>
               <tr>
                  <th>Anket Adı</th>
                  <th style="width:110px; text-align:center;">Soru</th>
                  <th style="width:140px; text-align:center;">Otomatik Gönderim</th>
                  <th style="width:120px; text-align:center;">Cevap</th>
                  <th style="width:100px; text-align:center;">Durum</th>
                  <th style="width:230px; text-align:right;">İşlemler</th>
               </tr>
            </thead>
            <tbody>
               @foreach($sablonlar as $s)
               <tr data-id="{{$s->id}}">
                  <td>
                     <b>{{$s->ad}}</b>
                     @if($s->varsayilan)<span class="badge-vars">Varsayılan</span>@endif
                     @if($s->aciklama)<div style="font-size:12px; color:#8a8295; margin-top:3px;">{{ mb_substr($s->aciklama, 0, 90) }}@if(mb_strlen($s->aciklama) > 90)…@endif</div>@endif
                  </td>
                  <td style="text-align:center;">
                     {{ $s->sorular_json ? count(json_decode($s->sorular_json, true) ?? []) : 0 }}
                  </td>
                  <td style="text-align:center; font-size:12.5px;">
                     @if($s->otomatik_gonder)
                        <i class="fa fa-clock-o" style="color:#10b981;"></i> Randevudan {{$s->gonder_saat_sonra}} sa sonra
                     @else
                        <span style="color:#8a8295;">Manuel</span>
                     @endif
                  </td>
                  <td style="text-align:center;">
                     @php $cevSayi = \App\AnketGonderim::where('sablon_id', $s->id)->where('cevaplandi',1)->count(); @endphp
                     <b>{{ $cevSayi }}</b>
                  </td>
                  <td style="text-align:center;">
                     @if($s->aktif)<span class="badge-aktif">Aktif</span>@else<span class="badge-pasif">Pasif</span>@endif
                  </td>
                  <td style="text-align:right;">
                     <button class="btn-mor-out" style="border-color:#10b981; color:#10b981;" onclick="testGonderAc({{$s->id}}, '{{addslashes($s->ad)}}')" title="Test gönder"><i class="fa fa-paper-plane"></i> Test</button>
                     <button class="btn-mor-out" onclick="sablonDuzenle({{$s->id}})"><i class="fa fa-edit"></i> Düzenle</button>
                     <button class="btn-mor-out" style="border-color:#ef4444; color:#ef4444;" onclick="sablonSil({{$s->id}}, '{{addslashes($s->ad)}}')"><i class="fa fa-trash"></i></button>
                  </td>
               </tr>
               @endforeach
            </tbody>
         </table>
      @endif
   </div>
</div>

{{-- Test Gönder Modal --}}
<div id="testGonderModal" class="modal fade" tabindex="-1">
   <div class="modal-dialog" style="max-width:480px; margin:auto;">
      <div class="modal-content" style="border-radius:14px; border:none; box-shadow:0 18px 50px rgba(92,0,142,.18);">
         <div class="modal-header" style="background:#faf5ff; border-bottom:1px solid #ece6f3; padding:14px 22px; border-radius:14px 14px 0 0;">
            <h4 style="color:#3a1a52; font-size:17px; font-weight:700; margin:0; display:flex; align-items:center; gap:10px;">
               <span style="width:34px; height:34px; background:#10b981; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#fff;"><i class="fa fa-paper-plane"></i></span>
               Test Gönder
            </h4>
            <button type="button" class="close" data-dismiss="modal">×</button>
         </div>
         <div class="modal-body" style="padding:18px 22px;">
            <div style="background:#f0fdf4; border-left:4px solid #10b981; padding:10px 14px; border-radius:6px; margin-bottom:14px; font-size:12.5px; color:#047857;">
               <i class="fa fa-info-circle"></i> Anketi nasıl göründüğünü görmek için kendi telefonuna SMS olarak gönder.
            </div>
            <div style="margin-bottom:8px; font-size:12px; color:#5C008E; font-weight:700; text-transform:uppercase; letter-spacing:.3px;">Şablon</div>
            <div id="testSablonAd" style="font-size:14px; font-weight:600; color:#3a1a52; margin-bottom:14px; padding:8px 12px; background:#fbfafd; border-radius:7px;"></div>

            <div style="margin-bottom:5px; font-size:12px; color:#5C008E; font-weight:700; text-transform:uppercase; letter-spacing:.3px;">Ad Soyad</div>
            <input type="text" id="testAdSoyad" class="form-control" placeholder="Adınız" value="" style="border:1.5px solid #dfd6ea; border-radius:7px; font-size:13.5px; padding:8px 11px; min-height:36px; margin-bottom:12px;">

            <div style="margin-bottom:5px; font-size:12px; color:#5C008E; font-weight:700; text-transform:uppercase; letter-spacing:.3px;">Telefon (10 hane)</div>
            <input type="text" id="testTelefon" class="form-control" placeholder="5XXXXXXXXX" maxlength="10" inputmode="numeric" pattern="[0-9]*" style="border:1.5px solid #dfd6ea; border-radius:7px; font-size:13.5px; padding:8px 11px; min-height:36px;">
         </div>
         <div class="modal-footer" style="padding:12px 22px; border-top:1px solid #ece6f3;">
            <button type="button" class="btn-mor-out" data-dismiss="modal">İptal</button>
            <button type="button" class="btn-mor" id="testGonderBtn" style="background:#10b981;" onclick="testGonderEt()"><i class="fa fa-paper-plane"></i> SMS Gönder</button>
         </div>
      </div>
   </div>
</div>

{{-- Şablon Oluştur/Düzenle Modal --}}
<div id="anketSablonModal" class="modal fade" tabindex="-1">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <h4>
               <span class="ikon-kutu"><i class="fa fa-comments"></i></span>
               <span id="anketModalBaslik">Yeni Anket Şablonu</span>
            </h4>
            <button type="button" class="close" data-dismiss="modal">×</button>
         </div>
         <div class="modal-body">
            {{ csrf_field() }}
            <input type="hidden" id="sablon_id_gizli" value="">

            <div class="row">
               <div class="col-md-12 form-group">
                  <label>Anket Adı *</label>
                  <input type="text" id="anket_ad_input" class="form-control" placeholder="Örn: Hizmet Sonrası Memnuniyet Anketi" maxlength="200">
               </div>
               <div class="col-md-12 form-group">
                  <label>Açıklama</label>
                  <textarea id="anket_aciklama_input" class="form-control" rows="2" placeholder="Anketin amacını kısa açıklayın (formun üstünde gösterilir)"></textarea>
               </div>
               <div class="col-md-6 form-group">
                  <label>Otomatik Gönderim</label>
                  <div style="background:#fbfafd; border:1px solid #dfd6ea; border-radius:7px; padding:8px 11px;">
                     <label style="margin:0; cursor:pointer; font-size:12.5px; color:#3a1a52; text-transform:none; letter-spacing:0;">
                        <input type="checkbox" id="anket_otomatik" style="transform:scale(1.1); margin-right:7px; accent-color:#5C008E;">
                        Randevu sonrası otomatik gönder
                     </label>
                  </div>
               </div>
               <div class="col-md-3 form-group">
                  <label>Saat Sonra</label>
                  <input type="number" id="anket_saat" class="form-control" value="24" min="1" max="720">
               </div>
               <div class="col-md-3 form-group">
                  <label>Varsayılan</label>
                  <div style="background:#fbfafd; border:1px solid #dfd6ea; border-radius:7px; padding:8px 11px;">
                     <label style="margin:0; cursor:pointer; font-size:12.5px; color:#3a1a52; text-transform:none; letter-spacing:0;">
                        <input type="checkbox" id="anket_varsayilan" style="transform:scale(1.1); margin-right:7px; accent-color:#5C008E;">
                        Varsayılan
                     </label>
                  </div>
               </div>
            </div>

            <hr style="margin:14px 0;">
            <label>Sorular *</label>
            <p style="color:#8a8295; font-size:12px; margin:0 0 10px;">Soru ekleyip ↑↓ butonlarıyla sıralayabilirsiniz. NPS ve yıldız soruları otomatik istatistik üretir.</p>

            <div id="sorular_konteyneri"></div>

            <div class="ekle-btn-grup mt-3">
               <div class="baslik">Skor Soruları</div>
               <button type="button" class="btn btn-mor" onclick="soruEkle('nps')"><i class="fa fa-line-chart"></i> NPS (0-10)</button>
               <button type="button" class="btn btn-mor" style="background:#f59e0b;" onclick="soruEkle('csat_yildiz')"><i class="fa fa-star"></i> Yıldızlı Memnuniyet (1-5)</button>
            </div>

            <div class="ekle-btn-grup mt-2">
               <div class="baslik">Cevap Soruları</div>
               <button type="button" class="btn btn-mor-out" onclick="soruEkle('evet_hayir')"><i class="fa fa-check-square-o"></i> Evet/Hayır</button>
               <button type="button" class="btn btn-mor-out" onclick="soruEkle('tek_secim')"><i class="fa fa-dot-circle-o"></i> Tek Seçim</button>
               <button type="button" class="btn btn-mor-out" onclick="soruEkle('cok_secim')"><i class="fa fa-check-square"></i> Çok Seçim</button>
               <button type="button" class="btn btn-mor-out" onclick="soruEkle('metin')"><i class="fa fa-minus"></i> Kısa Metin</button>
               <button type="button" class="btn btn-mor-out" onclick="soruEkle('uzun_metin')"><i class="fa fa-align-left"></i> Uzun Metin</button>
            </div>

            <div class="ekle-btn-grup mt-2">
               <div class="baslik">Yapı Elemanları</div>
               <button type="button" class="btn btn-mor-out" onclick="soruEkle('bolum_basligi')"><i class="fa fa-header"></i> Bölüm Başlığı</button>
               <button type="button" class="btn btn-mor-out" onclick="soruEkle('bilgi_metni')"><i class="fa fa-info-circle"></i> Bilgi Metni</button>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-mor-out" data-dismiss="modal">İptal</button>
            <button type="button" class="btn btn-mor" onclick="sablonKaydet()"><i class="fa fa-save"></i> Kaydet</button>
         </div>
      </div>
   </div>
</div>

<script>
var soruSayaci = 0;

var TIP_META = {
   nps:           { renk:'#5C008E', etiket:'NPS (0-10)',         badge:'#5C008E', secenekVar:false, zorunluVar:true },
   csat_yildiz:   { renk:'#f59e0b', etiket:'Yıldız (1-5)',       badge:'#f59e0b', secenekVar:false, zorunluVar:true },
   evet_hayir:    { renk:'#0ea5e9', etiket:'Evet/Hayır',         badge:'#0ea5e9', secenekVar:false, zorunluVar:true },
   tek_secim:     { renk:'#7c3aed', etiket:'Tek Seçim',          badge:'#7c3aed', secenekVar:true,  zorunluVar:true },
   cok_secim:     { renk:'#7c3aed', etiket:'Çok Seçim',          badge:'#7c3aed', secenekVar:true,  zorunluVar:true },
   metin:         { renk:'#10b981', etiket:'Kısa Metin',         badge:'#10b981', secenekVar:false, zorunluVar:true },
   uzun_metin:    { renk:'#10b981', etiket:'Uzun Metin',         badge:'#10b981', secenekVar:false, zorunluVar:true },
   bolum_basligi: { renk:'#475569', etiket:'Bölüm Başlığı',      badge:'#475569', secenekVar:false, zorunluVar:false },
   bilgi_metni:   { renk:'#0891b2', etiket:'Bilgi Metni',        badge:'#0891b2', secenekVar:false, zorunluVar:false },
};

function escapeHtml(t){ return (t==null?'':String(t)).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function soruEkle(tip, mevcut){
   soruSayaci++;
   var idx = soruSayaci;
   var soru = mevcut || { tip:tip, soru:'', zorunlu:true, secenekler:[] };
   var meta = TIP_META[tip] || TIP_META.metin;

   var soruInputu = '';
   if(tip === 'bolum_basligi'){
      soruInputu = '<input type="text" class="form-control soru-metni" placeholder="Bölüm başlığı..." value="'+escapeHtml(soru.soru||'')+'" style="font-weight:700; text-transform:uppercase;">';
   } else if(tip === 'bilgi_metni'){
      soruInputu = '<textarea class="form-control soru-metni" rows="2" placeholder="Bilgi/açıklama metni...">'+escapeHtml(soru.soru||'')+'</textarea>';
   } else {
      soruInputu = '<input type="text" class="form-control soru-metni" placeholder="Soru metnini yazın..." value="'+escapeHtml(soru.soru||'')+'">';
   }

   var secenekHtml = '';
   if(meta.secenekVar){
      var mevcutSec = (soru.secenekler && soru.secenekler.length) ? soru.secenekler.join('\n') : '';
      secenekHtml = '<div style="margin-top:7px;"><textarea class="form-control soru-secenekler" rows="3" placeholder="Her satıra bir seçenek (Çok Memnunum&#10;Memnunum&#10;Memnun Değilim)" style="font-size:12.5px;">'+escapeHtml(mevcutSec)+'</textarea><small style="color:#8a8295; font-size:11px;">Her satır ayrı bir seçenek olarak gösterilir.</small></div>';
   }

   var zorunluHtml = '';
   if(meta.zorunluVar){
      zorunluHtml = '<label style="margin:0; font-size:11.5px; color:#3a1a52; text-transform:none; letter-spacing:0; white-space:nowrap;"><input type="checkbox" class="soru-zorunlu" '+(soru.zorunlu!==false?'checked':'')+' style="margin-right:4px; accent-color:#5C008E;">Zorunlu</label>';
   } else {
      zorunluHtml = '<input type="hidden" class="soru-zorunlu" value="0">';
   }

   var html = ''+
      '<div class="soru-satiri" id="soru_'+idx+'" style="border-left-color:'+meta.renk+';">'+
         '<div style="display:flex; gap:9px; align-items:flex-start;">'+
            '<span class="tip-badge" style="background:'+meta.badge+'1a; color:'+meta.badge+'; flex-shrink:0; margin-top:5px;">'+meta.etiket+'</span>'+
            '<div style="flex:1; min-width:0;">'+soruInputu+secenekHtml+'</div>'+
            '<div style="display:flex; flex-direction:column; gap:3px; flex-shrink:0;">'+
               '<div style="display:flex; gap:3px;">'+
                  '<button type="button" class="btn btn-sm" style="padding:2px 7px; border:1px solid #dfd6ea; background:#fff;" onclick="soruYukari('+idx+')" title="Yukarı"><i class="fa fa-arrow-up"></i></button>'+
                  '<button type="button" class="btn btn-sm" style="padding:2px 7px; border:1px solid #dfd6ea; background:#fff;" onclick="soruAsagi('+idx+')" title="Aşağı"><i class="fa fa-arrow-down"></i></button>'+
                  '<button type="button" class="btn btn-sm" style="padding:2px 7px; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c;" onclick="soruSil('+idx+')" title="Sil"><i class="fa fa-trash"></i></button>'+
               '</div>'+
               '<div style="text-align:right;">'+zorunluHtml+'</div>'+
            '</div>'+
         '</div>'+
         '<input type="hidden" class="soru-tip" value="'+tip+'">'+
      '</div>';

   document.getElementById('sorular_konteyneri').insertAdjacentHTML('beforeend', html);
}

function soruSil(idx){ var el = document.getElementById('soru_'+idx); if(el) el.remove(); }
function soruYukari(idx){ var el = document.getElementById('soru_'+idx); if(!el) return; var prev = el.previousElementSibling; if(prev && prev.classList.contains('soru-satiri')) el.parentNode.insertBefore(el, prev); }
function soruAsagi(idx){ var el = document.getElementById('soru_'+idx); if(!el) return; var next = el.nextElementSibling; if(next && next.classList.contains('soru-satiri')) el.parentNode.insertBefore(next, el); }

function sorulariTopla(){
   var sorular = [];
   document.querySelectorAll('#sorular_konteyneri .soru-satiri').forEach(function(el){
      var tip = el.querySelector('.soru-tip').value;
      var metni = el.querySelector('.soru-metni').value.trim();
      var zorEl = el.querySelector('.soru-zorunlu');
      var zorunlu = zorEl && zorEl.type === 'checkbox' ? zorEl.checked : false;
      var s = { tip: tip, soru: metni, zorunlu: zorunlu };
      var secEl = el.querySelector('.soru-secenekler');
      if(secEl){
         var arr = secEl.value.split('\n').map(function(x){ return x.trim(); }).filter(function(x){ return x.length>0; });
         s.secenekler = arr;
      }
      sorular.push(s);
   });
   return sorular;
}

function yeniSablonAc(){
   document.getElementById('sablon_id_gizli').value = '';
   document.getElementById('anket_ad_input').value = '';
   document.getElementById('anket_aciklama_input').value = '';
   document.getElementById('anket_otomatik').checked = true;
   document.getElementById('anket_saat').value = 24;
   document.getElementById('anket_varsayilan').checked = false;
   document.getElementById('sorular_konteyneri').innerHTML = '';
   soruSayaci = 0;
   document.getElementById('anketModalBaslik').textContent = 'Yeni Anket Şablonu';

   // Default soru seti (best-practice NPS + CSAT + open-ended)
   soruEkle('nps', { tip:'nps', soru:'Bizi bir arkadaşınıza tavsiye etme olasılığınız 0-10 arasında nedir?', zorunlu:true });
   soruEkle('csat_yildiz', { tip:'csat_yildiz', soru:'Genel olarak hizmet kalitemizi nasıl değerlendirirsiniz?', zorunlu:true });
   soruEkle('csat_yildiz', { tip:'csat_yildiz', soru:'Personelimizin ilgisi ve profesyonelliği nasıldı?', zorunlu:false });
   soruEkle('uzun_metin', { tip:'uzun_metin', soru:'Eklemek istediğiniz başka bir görüş var mı?', zorunlu:false });

   $('#anketSablonModal').modal('show');
}

function ornekSablonOlustur(){
   yeniSablonAc();
   document.getElementById('anket_ad_input').value = 'Hizmet Sonrası Memnuniyet Anketi';
   document.getElementById('anket_aciklama_input').value = 'Görüşleriniz bizim için çok değerli. Lütfen 1 dakikanızı ayırarak deneyiminizi değerlendirin.';
}

function sablonDuzenle(id){
   $.get('/isletmeyonetim/anket-sablon-getir?sube={{$isletme->id}}&id='+id, function(resp){
      if(resp.hata){ alert('Şablon bulunamadı.'); return; }
      document.getElementById('sablon_id_gizli').value = resp.id;
      document.getElementById('anket_ad_input').value = resp.ad || '';
      document.getElementById('anket_aciklama_input').value = resp.aciklama || '';
      document.getElementById('anket_otomatik').checked = !!parseInt(resp.otomatik_gonder);
      document.getElementById('anket_saat').value = resp.gonder_saat_sonra || 24;
      document.getElementById('anket_varsayilan').checked = !!parseInt(resp.varsayilan);
      document.getElementById('sorular_konteyneri').innerHTML = '';
      soruSayaci = 0;
      var sorular = [];
      try { sorular = resp.sorular_json ? JSON.parse(resp.sorular_json) : []; } catch(e){}
      sorular.forEach(function(s){ soruEkle(s.tip, s); });
      document.getElementById('anketModalBaslik').textContent = 'Anket Şablonu Düzenle';
      $('#anketSablonModal').modal('show');
   });
}

function sablonKaydet(){
   var ad = document.getElementById('anket_ad_input').value.trim();
   if(!ad){ alert('Anket adı zorunlu.'); return; }
   var sorular = sorulariTopla();
   var cevapVerenSorular = sorular.filter(function(s){ return ['bolum_basligi','bilgi_metni'].indexOf(s.tip) === -1; });
   if(cevapVerenSorular.length === 0){ alert('En az bir cevap verilebilir soru ekleyin.'); return; }
   for(var i=0; i<sorular.length; i++){
      if(!sorular[i].soru || !sorular[i].soru.trim()){ alert((i+1)+'. soru metni boş.'); return; }
   }

   var sablonId = document.getElementById('sablon_id_gizli').value;
   var url = sablonId ? '/isletmeyonetim/anket-sablon-guncelle' : '/isletmeyonetim/anket-sablon-kaydet';
   var data = {
      _token: '{{csrf_token()}}',
      sube: {{$isletme->id}},
      ad: ad,
      aciklama: document.getElementById('anket_aciklama_input').value,
      sorular_json: JSON.stringify(sorular),
      otomatik_gonder: document.getElementById('anket_otomatik').checked ? 1 : 0,
      gonder_saat_sonra: document.getElementById('anket_saat').value,
      varsayilan: document.getElementById('anket_varsayilan').checked ? 1 : 0
   };
   if(sablonId) data.sablon_id = sablonId;

   $.post(url, data, function(resp){
      if(resp.basarili){ location.reload(); }
      else alert('Hata: '+(resp.mesaj||'Bilinmeyen hata'));
   }).fail(function(){ alert('Sunucu hatası.'); });
}

function sablonSil(id, ad){
   if(!confirm('"'+ad+'" şablonunu silmek istediğinize emin misiniz?')) return;
   $.post('/isletmeyonetim/anket-sablon-sil', {
      _token:'{{csrf_token()}}', sube:{{$isletme->id}}, sablon_id:id
   }, function(resp){
      if(resp.basarili){
         if(resp.mesaj) alert(resp.mesaj);
         location.reload();
      } else alert('Hata: '+(resp.mesaj||''));
   });
}

// Modal: tam viewport ortası, body'ye taşı (memory'deki feedback_modal_tasarim.md kuralı)
$(document).on('show.bs.modal', '#anketSablonModal', function(){
   $(this).appendTo('body');
});
$(document).on('show.bs.modal', '#testGonderModal', function(){
   $(this).appendTo('body');
});

var testSablonId = null;

function testGonderAc(sablonId, sablonAd){
   testSablonId = sablonId;
   document.getElementById('testSablonAd').textContent = sablonAd;
   document.getElementById('testAdSoyad').value = '';
   document.getElementById('testTelefon').value = '';
   $('#testGonderModal').modal('show');
   setTimeout(function(){ document.getElementById('testTelefon').focus(); }, 250);
}

function testGonderEt(){
   var ad = document.getElementById('testAdSoyad').value.trim() || 'Test';
   var tel = document.getElementById('testTelefon').value.replace(/\D/g,'');
   if(tel.length !== 10){ alert('Telefon 10 haneli olmalı (5XXXXXXXXX).'); return; }

   var btn = document.getElementById('testGonderBtn');
   btn.disabled = true;
   btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Gönderiliyor...';

   $.post('/isletmeyonetim/anket-manuel-gonder', {
      _token: '{{csrf_token()}}',
      sube: {{$isletme->id}},
      sablon_id: testSablonId,
      ad_soyad: ad,
      cep_telefon: tel,
      user_id: 0
   }, function(resp){
      btn.disabled = false;
      btn.innerHTML = '<i class="fa fa-paper-plane"></i> SMS Gönder';
      if(resp.basarili){
         $('#testGonderModal').modal('hide');
         alert('SMS gönderildi. Telefonunuza gelen linke tıklayarak anketi doldurabilirsiniz.');
      } else {
         alert('Hata: ' + (resp.mesaj || 'Bilinmeyen hata'));
      }
   }).fail(function(){
      btn.disabled = false;
      btn.innerHTML = '<i class="fa fa-paper-plane"></i> SMS Gönder';
      alert('Sunucu hatası.');
   });
}
</script>
@endsection
