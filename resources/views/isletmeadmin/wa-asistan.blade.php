@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
@php
   $J = function ($v) { return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); };
@endphp
<style>
   .wa-sayfa { --mor:#5C008E; --mor2:#7B2FB8; --mor-acik:#f4edfa; --yesil:#16a34a; --kirmizi:#dc2626; --gri:#6b7280; --cizgi:#ece6f3; --zemin:#fff; color:#2a1f48; }
   .wa-sayfa * { box-sizing:border-box; }
   .wa-sekmeler { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:16px; }
   .wa-sekme { border:1px solid var(--cizgi); background:#fff; color:#4b4163; padding:8px 14px; border-radius:999px; font-size:13.5px; font-weight:600; cursor:pointer; }
   .wa-sekme.aktif { background:var(--mor); border-color:var(--mor); color:#fff; }
   .wa-panel { display:none; } .wa-panel.aktif { display:block; }
   .wa-kart { background:var(--zemin); border:1px solid var(--cizgi); border-radius:14px; padding:16px; margin-bottom:14px; }
   .wa-kart h4 { font-size:15px; font-weight:700; margin:0 0 10px; color:#2a1f48; }
   .wa-alt { font-size:12.5px; color:var(--gri); }
   .wa-hero { background:linear-gradient(135deg,#5C008E,#7B2FB8 60%,#9D5DC7); color:#fff; border-radius:16px; padding:20px 22px; margin-bottom:14px; display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:center; }
   .wa-hero .buyuk { font-size:30px; font-weight:800; line-height:1.1; }
   .wa-hero .kucuk { font-size:13px; opacity:.9; }
   .wa-hero .kat { background:rgba(255,255,255,.16); border-radius:12px; padding:10px 14px; text-align:center; min-width:150px; }
   .wa-hero .kat b { font-size:24px; display:block; }
   .wa-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(190px,1fr)); gap:12px; margin-bottom:14px; }
   .wa-kpi { background:#fff; border:1px solid var(--cizgi); border-radius:12px; padding:13px 14px; }
   .wa-kpi .d { font-size:22px; font-weight:800; color:var(--mor); }
   .wa-kpi .e { font-size:13px; font-weight:600; color:#3b3155; margin-top:2px; }
   .wa-kpi .a { font-size:12px; color:var(--gri); margin-top:3px; }
   .wa-bar { height:10px; background:#f0eaf7; border-radius:99px; overflow:hidden; }
   .wa-bar > i { display:block; height:100%; background:linear-gradient(90deg,#7B2FB8,#5C008E); border-radius:99px; }
   .wa-bar.uyari > i { background:linear-gradient(90deg,#f59e0b,#d97706); } .wa-bar.dolu > i { background:#dc2626; }
   .wa-huni { display:flex; flex-direction:column; gap:8px; }
   .wa-huni .s { display:flex; align-items:center; gap:10px; font-size:13px; }
   .wa-huni .s span:first-child { width:150px; color:#4b4163; font-weight:600; }
   .wa-huni .s .wa-bar { flex:1; height:14px; }
   .wa-huni .s b { width:48px; text-align:right; }
   .wa-tablo { width:100%; border-collapse:collapse; font-size:13px; }
   .wa-tablo th { text-align:left; color:var(--gri); font-weight:600; padding:8px 6px; border-bottom:1px solid var(--cizgi); white-space:nowrap; }
   .wa-tablo td { padding:8px 6px; border-bottom:1px solid #f4f0f8; vertical-align:top; }
   .wa-rozet { display:inline-block; padding:2px 9px; border-radius:99px; font-size:11.5px; font-weight:600; background:#f1f5f9; color:#475569; white-space:nowrap; }
   .wa-rozet.y { background:#dcfce7; color:#166534; } .wa-rozet.m { background:var(--mor-acik); color:var(--mor); } .wa-rozet.k { background:#fee2e2; color:#991b1b; } .wa-rozet.s { background:#fef3c7; color:#92400e; }
   .wa-iki { display:grid; grid-template-columns:minmax(260px,360px) 1fr; gap:14px; }
   @media (max-width: 860px) { .wa-iki { grid-template-columns:1fr; } .wa-hero .buyuk { font-size:24px; } }
   .wa-liste { max-height:620px; overflow:auto; display:flex; flex-direction:column; gap:6px; }
   .wa-oge { border:1px solid var(--cizgi); border-radius:10px; padding:9px 11px; cursor:pointer; background:#fff; }
   .wa-oge:hover, .wa-oge.secili { border-color:var(--mor2); background:#faf6fd; }
   .wa-oge .ust { display:flex; justify-content:space-between; gap:6px; font-size:13px; font-weight:700; }
   .wa-oge .orta { font-size:12.5px; color:#4b4163; margin:3px 0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
   .wa-sohbet { display:flex; flex-direction:column; gap:8px; max-height:560px; overflow:auto; padding:6px 2px; }
   .wa-balon { max-width:82%; padding:8px 11px; border-radius:12px; font-size:13.5px; white-space:pre-wrap; word-wrap:break-word; line-height:1.4; }
   .wa-balon.musteri { align-self:flex-start; background:#f1f5f9; }
   .wa-balon.asistan { align-self:flex-end; background:#efe6f8; }
   .wa-balon.personel { align-self:flex-end; background:#dcfce7; }
   .wa-balon .r { display:block; font-size:11px; font-weight:700; color:var(--gri); margin-bottom:2px; }
   .wa-btn { border:none; border-radius:9px; padding:8px 14px; font-size:13px; font-weight:600; cursor:pointer; background:var(--mor); color:#fff; }
   .wa-btn.ikincil { background:#fff; color:var(--mor); border:1px solid var(--mor); }
   .wa-btn:disabled { opacity:.55; cursor:default; }
   .wa-girdi { border:1px solid #ddd3ea; border-radius:9px; padding:7px 10px; font-size:13px; width:100%; background:#fff; }
   .wa-satir { display:flex; gap:8px; flex-wrap:wrap; align-items:center; margin-bottom:12px; }
   .wa-satir > * { flex:0 0 auto; }
   .wa-anahtar { display:flex; justify-content:space-between; gap:12px; align-items:flex-start; padding:12px 0; border-bottom:1px solid #f4f0f8; }
   .wa-anahtar .t b { font-size:14px; } .wa-anahtar .t div { font-size:12.5px; color:var(--gri); margin-top:2px; }
   .wa-bos { text-align:center; color:var(--gri); font-size:13px; padding:26px 10px; }
   .wa-gorseller { display:flex; gap:8px; flex-wrap:wrap; margin:8px 0; }
   .wa-gorseller figure { margin:0; width:120px; font-size:11.5px; color:#4b4163; }
   .wa-gorseller img { width:120px; height:120px; object-fit:cover; border-radius:10px; border:1px solid var(--cizgi); }
   .wa-bilgi { background:#faf6fd; border:1px dashed #d6c4ea; border-radius:12px; padding:12px 14px; font-size:13px; color:#4b4163; margin-bottom:14px; }
   .wa-toast { position:fixed; left:50%; bottom:24px; transform:translateX(-50%); background:#2a1f48; color:#fff; padding:10px 18px; border-radius:10px; font-size:13.5px; z-index:99999; display:none; }
</style>

<div class="wa-sayfa">
   @if(!$waAcik)
      <div class="wa-bilgi"><b>WhatsApp asistanı bu işletmede henüz açık değil.</b> Açmak için bizimle iletişime geçin; aşağıdaki ayarları şimdiden yapabilirsiniz.</div>
   @endif

   <div class="wa-sekmeler" id="waSekmeler">
      <button class="wa-sekme aktif" data-p="kazanc">Kazandırdıkları</button>
      <button class="wa-sekme" data-p="sohbet">Sohbetler</button>
      <button class="wa-sekme" data-p="gk">Kaçan & Geri Kazanılan</button>
      <button class="wa-sekme" data-p="gorsel">Fotoğraf Fiyatları</button>
      <button class="wa-sekme" data-p="ogr">Öğrenilen Cevaplar</button>
      <button class="wa-sekme" data-p="ayar">Ayarlar</button>
   </div>

   {{-- ================= KAZANDIRDIKLARI ================= --}}
   <div class="wa-panel aktif" id="p-kazanc">
      <div class="wa-satir">
         <select id="waAy" class="wa-girdi" style="width:auto"></select>
         <span class="wa-alt" id="waAyNot"></span>
      </div>
      <div id="waKazanc"><div class="wa-bos">Yükleniyor…</div></div>
   </div>

   {{-- ================= SOHBETLER ================= --}}
   <div class="wa-panel" id="p-sohbet">
      <div class="wa-satir">
         <input type="date" id="waBas" class="wa-girdi" style="width:auto">
         <input type="date" id="waBit" class="wa-girdi" style="width:auto">
         <input type="text" id="waAra" class="wa-girdi" style="width:220px" placeholder="Ad veya telefon ara">
         <button class="wa-btn ikincil" id="waSohbetYukle">Listele</button>
      </div>
      <div class="wa-iki">
         <div class="wa-kart" style="padding:10px"><div class="wa-liste" id="waSohbetListe"><div class="wa-bos">Yükleniyor…</div></div></div>
         <div class="wa-kart" id="waSohbetDetay"><div class="wa-bos">Soldan bir sohbet seçin.</div></div>
      </div>
   </div>

   {{-- ================= GERI KAZANMA ================= --}}
   <div class="wa-panel" id="p-gk">
      <div class="wa-kart">
         <h4>Kaçan müşteriler</h4>
         <div class="wa-alt" style="margin-bottom:8px">Asistandan fiyat, bilgi, müsait saat ya da fotoğrafa fiyat alıp randevu oluşturmayan müşteriler. Asistan ayarlardaki süre dolunca bu müşterilere bir kez nazikçe yazar.</div>
         <div id="waKacan"><div class="wa-bos">Yükleniyor…</div></div>
      </div>
      <div class="wa-kart">
         <h4>Geri kazanma mesajları</h4>
         <div id="waGkListe"><div class="wa-bos">Yükleniyor…</div></div>
      </div>
   </div>

   {{-- ================= FOTOGRAF FIYATLARI ================= --}}
   <div class="wa-panel" id="p-gorsel">
      <div class="wa-bilgi">Müşteri WhatsApp'tan model fotoğrafı gönderdiğinde asistan fotoğraftaki uygulamaları tanır; <b>fiyatı aşağıdaki kurallarınızdan sistem hesaplar</b> (asistan fiyat uydurmaz). Ana işlem için bir hizmetinizi seçin ya da sabit fiyat yazın; ekler için fiyat yazın. Boş bıraktığınız ekler fiyata eklenmez. Ana işlemin kuralı yoksa müşteri size aktarılır.</div>
      <div class="wa-kart">
         <div class="wa-satir">
            <label class="wa-alt" for="waSektor">Sektör</label>
            <select id="waSektor" class="wa-girdi" style="width:auto"></select>
         </div>
         <div id="waKuralForm"></div>
         <div style="margin-top:12px"><label class="wa-alt" for="waGorselNot">Fiyatın altına eklenecek not (isteğe bağlı)</label>
            <textarea id="waGorselNot" class="wa-girdi" rows="2" maxlength="500" placeholder="Örn. Mevcut protezin sökümü 150 TL eklenir."></textarea></div>
         <div style="margin-top:12px"><button class="wa-btn" id="waKuralKaydet">Kuralları kaydet</button></div>
      </div>
   </div>

   {{-- ================= OGRENILEN ================= --}}
   <div class="wa-panel" id="p-ogr">
      <div class="wa-bilgi">Asistan genel bir soruya (ör. "otopark var mı") yapay zekayla bir kez cevap verince o cevabı öğrenir; aynı soru sonra <b>ücretsiz ve anında</b> cevaplanır. Yanlış ya da güncelliğini yitirmiş bir cevabı silebilirsiniz; asistan bir dahaki sefere yeniden düşünür. Öğrenilen cevaplar 30 günde bir yenilenir.</div>
      <div class="wa-kart"><div id="waOgrListe"><div class="wa-bos">Yükleniyor…</div></div></div>
   </div>

   {{-- ================= AYARLAR ================= --}}
   <div class="wa-panel" id="p-ayar">
      <div class="wa-kart">
         <div class="wa-anahtar"><div class="t"><b>Fiyat sorularını cevaplasın</b><div>"Protez tırnak ne kadar?" gibi sorulara hizmet listenizdeki fiyatla cevap verir. Kapalıysa fiyat soran müşteri size aktarılır.</div></div><input type="checkbox" data-ayar="fiyat_soyle"></div>
         <div class="wa-anahtar"><div class="t"><b>Fotoğraftan fiyat versin</b><div>Model fotoğrafına "Fotoğraf Fiyatları" kurallarınızla fiyat verir.</div></div><input type="checkbox" data-ayar="gorsel_fiyat"></div>
         <div class="wa-anahtar"><div class="t"><b>Randevudan sonra memnuniyet sorsun</b><div>Randevu oluşturulunca tek satırlık 👍 / 👎 sorusu eklenir.</div></div><input type="checkbox" data-ayar="memnuniyet_sor"></div>
         <div class="wa-anahtar"><div class="t"><b>Kaçan müşteriye tekrar yazsın</b><div>Fiyat/bilgi alıp randevu oluşturmayan müşteriye belirlediğiniz süre sonunda, mesai saatinde bir kez yazar ve gerçek boş saatlerinizi önerir. Müşteri "dur" yazarsa bir daha yazılmaz.</div></div><input type="checkbox" data-ayar="geri_kazanim"></div>
         <div class="wa-satir" style="margin-top:12px">
            <label class="wa-alt" for="waBekle">Kaç saat sonra yazsın</label>
            <input type="number" id="waBekle" class="wa-girdi" style="width:100px" min="2" max="168">
         </div>
         <label class="wa-alt" for="waKampanya">Fiyatı yüksek bulan müşteriye eklenecek teklif cümleniz (isteğe bağlı — boşsa teklif yapılmaz; asistan kendisi indirim uydurmaz)</label>
         <textarea id="waKampanya" class="wa-girdi" rows="2" maxlength="500" placeholder="Örn. Bu haftaya özel protez tırnakta %10 indirimimiz var."></textarea>
         <div style="margin-top:12px"><button class="wa-btn" id="waAyarKaydet">Ayarları kaydet</button></div>
      </div>
   </div>
</div>
<div class="wa-toast" id="waToast"></div>

<script>
(function () {
   var SUBE = {!! $J(isset($_GET['sube']) ? (string) $isletme->id : '') !!};
   var CSRF = {!! $J(csrf_token()) !!};
   var AYAR = {!! $J($waAyar) !!};
   var SEKTORLER = {!! $J($waSektorler) !!};
   var KURALLAR = {!! $J($waKurallar) !!};
   var HIZMETLER = {!! $J($waHizmetler) !!};
   var KOK = '/isletmeyonetim/whatsapp-asistan';

   function el(id) { return document.getElementById(id); }
   function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
   function url(yol, p) { p = p || {}; if (SUBE) p.sube = SUBE; var q = Object.keys(p).map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(p[k]); }).join('&'); return KOK + yol + (q ? '?' + q : ''); }
   function al(yol, p) { return fetch(url(yol, p), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } }).then(function (r) { return r.json(); }); }
   function gonder(yol, veri) {
      return fetch(url(yol), { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify(veri || {}) })
         .then(function (r) { return r.json(); });
   }
   function tl(x) { x = Number(x || 0); return x.toLocaleString('tr-TR', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' TL'; }
   function sayi(x) { return Number(x || 0).toLocaleString('tr-TR'); }
   function tarih(s) { if (!s) return ''; var d = new Date(String(s).replace(' ', 'T')); return isNaN(d) ? esc(s) : d.toLocaleDateString('tr-TR', { day: '2-digit', month: 'short' }) + ' ' + d.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' }); }
   function sure(sn) { if (sn == null) return '—'; return sn < 60 ? sn + ' sn' : Math.round(sn / 60) + ' dk'; }
   function toast(m) { var t = el('waToast'); t.textContent = m; t.style.display = 'block'; clearTimeout(t._z); t._z = setTimeout(function () { t.style.display = 'none'; }, 2600); }
   function hata() { toast('Bir hata oluştu, lütfen tekrar deneyin.'); }

   // ---------- sekmeler
   var yuklendi = {}, SEKME_YUKLE = { sohbet: sohbetListe, gk: gkYukle, ogr: ogrYukle };
   el('waSekmeler').addEventListener('click', function (e) {
      var b = e.target.closest('.wa-sekme'); if (!b) return;
      document.querySelectorAll('.wa-sekme').forEach(function (x) { x.classList.toggle('aktif', x === b); });
      document.querySelectorAll('.wa-panel').forEach(function (x) { x.classList.toggle('aktif', x.id === 'p-' + b.dataset.p); });
      if (!yuklendi[b.dataset.p] && SEKME_YUKLE[b.dataset.p]) { yuklendi[b.dataset.p] = 1; SEKME_YUKLE[b.dataset.p](); }
   });

   // ---------- KAZANDIRDIKLARI
   var aySec = el('waAy'), aylar = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];
   for (var i = 0; i < 12; i++) { var d = new Date(); d.setDate(1); d.setMonth(d.getMonth() - i); var v = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0'); aySec.insertAdjacentHTML('beforeend', '<option value="' + v + '">' + aylar[d.getMonth()] + ' ' + d.getFullYear() + '</option>'); }
   aySec.addEventListener('change', function () { kazancYukle(); yuklendi.gk = 0; });
   var sonOzet = null;

   function kpi(d, e, a) { return '<div class="wa-kpi"><div class="d">' + d + '</div><div class="e">' + e + '</div>' + (a ? '<div class="a">' + a + '</div>' : '') + '</div>'; }
   function kazancYukle() {
      el('waKazanc').innerHTML = '<div class="wa-bos">Yükleniyor…</div>';
      al('/ozet', { ay: aySec.value }).then(function (r) {
         if (!r.ok) { el('waKazanc').innerHTML = '<div class="wa-bos">' + esc(r.hata || 'Yüklenemedi') + '</div>'; return; }
         sonOzet = r.ozet; var o = r.ozet, p = r.paket, h = '';
         var kz = o.kazanc;
         h += '<div class="wa-hero"><div><div class="kucuk">WhatsApp asistanınızın bu ay getirdiği</div><div class="buyuk">' + tl(kz.gerceklesen > 0 ? kz.gerceklesen : kz.beklenen) + '</div>'
            + '<div class="kucuk">' + (kz.gerceklesen > 0 ? 'gerçekleşen kazanç (salona gelen müşteriler)' : 'alınan randevuların toplam tutarı') + ' · ' + sayi(o.randevu.adet + o.geri_kazanim.randevu) + ' randevu</div></div>'
            + (kz.kat ? '<div class="kat"><b>' + String(kz.kat).replace('.', ',') + ' kat</b><span class="kucuk">pakete ödediğiniz ' + tl(kz.paket_ucret) + '’nin</span></div>' : '') + '</div>';
         if (p && p.var) {
            var cls = p.kapali ? 'dolu' : (p.seviye ? 'uyari' : '');
            h += '<div class="wa-kart"><h4>Paket kullanımı</h4><div class="wa-alt" style="margin-bottom:8px">Bu ay ' + sayi(o.cevap.toplam) + ' cevap · '
               + sayi(o.cevap.ucretsiz) + ' ücretsiz · <b>' + sayi(p.kullanilan) + ' paketten</b> · kalan ' + sayi(p.kalan) + ' / ' + sayi(p.dahil) + '</div>'
               + '<div class="wa-bar ' + cls + '"><i style="width:' + p.yuzde + '%"></i></div>'
               + (p.kapali ? '<div class="wa-alt" style="color:#dc2626;margin-top:6px"><b>Paket doldu:</b> asistan ay sonuna kadar cevap vermiyor. Ek paket için bizimle iletişime geçin.</div>' : '')
               + '<div class="wa-alt" style="margin-top:6px">Yalnız yapay zekâ cevapları paketten düşer; fiyat, bilgi, selam ve öğrenilmiş cevaplar ücretsizdir.</div></div>';
         } else {
            h += '<div class="wa-kart"><div class="wa-alt">Bu ay ' + sayi(o.cevap.toplam) + ' cevap verildi (' + sayi(o.cevap.ucretsiz) + ' ücretsiz, ' + sayi(o.cevap.ai) + ' yapay zekâ).</div></div>';
         }
         h += '<div class="wa-grid">'
            + kpi(sayi(o.randevu.adet), 'Asistanın aldığı randevu', tl(o.randevu.tutar) + (o.randevu.geldi ? ' · ' + sayi(o.randevu.geldi) + ' müşteri geldi' : ''))
            + kpi(tl(kz.mesai_disi), 'Mesai dışı kazanç', sayi(o.randevu.mesai_disi) + ' randevu personel yokken alındı')
            + kpi(sayi(o.geri_kazanim.randevu), 'Geri kazanılan müşteri', sayi(o.geri_kazanim.yazildi) + ' kaçan müşteriye yazıldı · ' + tl(o.geri_kazanim.tutar))
            + kpi(sayi(o.randevu.yeni_musteri), 'Yeni müşteri', 'WhatsApp’tan ilk kez gelip randevu alan')
            + kpi(String(o.cevap.personel_saat).replace('.', ',') + ' saat', 'Personelinize kazandırılan zaman', sayi(o.cevap.toplam) + ' mesaj cevaplandı')
            + kpi(sure(o.cevap.medyan_sure_sn), 'Ortalama cevap süresi', sayi(o.cevap.mesai_disi) + ' mesaj mesai dışında cevaplandı')
            + kpi(o.sohbet.cozulen_orani == null ? '—' : '%' + o.sohbet.cozulen_orani, 'Tek başına çözdüğü', 'personele aktarılmadan biten yazışmalar')
            + kpi(o.sohbet.memnuniyet_orani == null ? '—' : '%' + o.sohbet.memnuniyet_orani, 'Müşteri memnuniyeti', sayi(o.sohbet.memnun) + ' 👍 · ' + sayi(o.sohbet.memnun_degil) + ' 👎')
            + kpi(sayi(o.gorsel.fiyat_verildi), 'Fotoğrafa fiyat verildi', sayi(o.gorsel.randevu) + ' tanesi randevuya döndü')
            + kpi(sayi(o.randevu.guncelle + o.randevu.iptal_islem), 'Telefonsuz değişiklik/iptal', sayi(o.randevu.teyit) + ' hatırlatma teyidi')
            + '</div>';
         var hu = o.huni, max = Math.max(1, hu.yazdi);
         h += '<div class="wa-iki"><div class="wa-kart"><h4>Müşteri yolculuğu</h4><div class="wa-huni">'
            + [['Yazdı', hu.yazdi], ['Bilgi / fiyat aldı', hu.bilgi], ['Randevu aldı', hu.randevu], ['Salona geldi', hu.geldi]].map(function (s) {
               return '<div class="s"><span>' + s[0] + '</span><div class="wa-bar"><i style="width:' + Math.round(s[1] / max * 100) + '%"></i></div><b>' + sayi(s[1]) + '</b></div>'; }).join('')
            + '</div><div class="wa-alt" style="margin-top:8px">"Geldi" sayısı panelde randevuya "geldi" işaretlendikçe güncellenir.</div></div>'
            + '<div class="wa-kart"><h4>Aksiyon bekleyenler</h4>'
            + (o.aktar_bekleyen.length ? '<div style="margin-bottom:10px"><span class="wa-rozet s">' + o.aktar_bekleyen.length + ' müşteri sizden dönüş bekliyor</span><div class="wa-alt" style="margin-top:6px">' + o.aktar_bekleyen.map(function (x) { return esc(x.ad || 'Müşteri') + ' (' + esc(x.telefon || 'gizli numara') + ')'; }).join(', ') + '</div></div>' : '')
            + '<div><b style="font-size:13px">' + sayi(o.kacan.length) + ' müşteri bilgi alıp randevu almadı</b><div class="wa-alt">Asistan uygun zamanda bu müşterilere bir kez yazar. Listeyi "Kaçan & Geri Kazanılan" sekmesinde görebilirsiniz.</div></div>'
            + '<div style="margin-top:12px"><b style="font-size:13px">En çok sorulan hizmetler</b><div class="wa-alt">' + (Object.keys(o.populer_hizmet).length ? Object.keys(o.populer_hizmet).map(function (k) { return esc(k) + ' (' + o.populer_hizmet[k] + ')'; }).join(' · ') : '—') + '</div></div>'
            + '<div style="margin-top:8px"><b style="font-size:13px">En yoğun saatler</b><div class="wa-alt">' + (Object.keys(o.yogun_saat).length ? Object.keys(o.yogun_saat).map(function (k) { return String(k).padStart(2, '0') + ':00'; }).join(' · ') : '—') + '</div></div>'
            + '</div></div>';
         el('waKazanc').innerHTML = h;
      }).catch(function () { el('waKazanc').innerHTML = '<div class="wa-bos">Rapor yüklenemedi.</div>'; });
   }
   kazancYukle();

   // ---------- SOHBETLER
   var bugun = new Date(), once = new Date(); once.setDate(once.getDate() - 6);
   el('waBas').value = once.toISOString().slice(0, 10); el('waBit').value = bugun.toISOString().slice(0, 10);
   el('waSohbetYukle').addEventListener('click', sohbetListe);
   el('waAra').addEventListener('keydown', function (e) { if (e.key === 'Enter') sohbetListe(); });
   function durumRozet(s) { var c = /Randevu alındı|teyit/.test(s) ? 'y' : (/aktarıldı|Personelde/.test(s) ? 's' : (/iptal|durduruldu|Bot/.test(s) ? 'k' : 'm')); return '<span class="wa-rozet ' + c + '">' + esc(s) + '</span>'; }
   function sohbetListe() {
      el('waSohbetListe').innerHTML = '<div class="wa-bos">Yükleniyor…</div>';
      al('/sohbetler', { baslangic: el('waBas').value, bitis: el('waBit').value, q: el('waAra').value }).then(function (r) {
         if (!r.ok) { el('waSohbetListe').innerHTML = '<div class="wa-bos">' + esc(r.hata || '') + '</div>'; return; }
         if (!r.liste.length) { el('waSohbetListe').innerHTML = '<div class="wa-bos">Bu tarihlerde sohbet yok.</div>'; return; }
         el('waSohbetListe').innerHTML = r.liste.map(function (o) {
            return '<div class="wa-oge" data-id="' + o.id + '"><div class="ust"><span>' + esc(o.ad || o.telefon || 'Müşteri') + '</span><span class="wa-alt">' + tarih(o.tarih) + '</span></div>'
               + '<div class="orta">' + esc(o.son_mesaj) + '</div><div>' + durumRozet(o.durum) + (o.gorsel ? ' <span class="wa-rozet">📷 ' + o.gorsel + '</span>' : '')
               + (o.geri_kazanim ? ' <span class="wa-rozet m">geri kazanma</span>' : '') + (o.memnuniyet == null ? '' : (Number(o.memnuniyet) === 1 ? ' 👍' : ' 👎')) + '</div></div>';
         }).join('');
      }).catch(hata);
   }
   el('waSohbetListe').addEventListener('click', function (e) {
      var o = e.target.closest('.wa-oge'); if (!o) return;
      document.querySelectorAll('.wa-oge').forEach(function (x) { x.classList.toggle('secili', x === o); });
      sohbetAc(o.dataset.id);
   });
   function sohbetAc(id) {
      el('waSohbetDetay').innerHTML = '<div class="wa-bos">Yükleniyor…</div>';
      al('/sohbet/' + id).then(function (r) {
         if (!r.ok) { el('waSohbetDetay').innerHTML = '<div class="wa-bos">' + esc(r.hata || '') + '</div>'; return; }
         var s = r.sohbet, h = '<div class="wa-satir" style="justify-content:space-between"><div><b>' + esc(s.ad || 'Müşteri') + '</b> <span class="wa-alt">' + esc(s.telefon || 'gizli numara') + '</span><div>' + durumRozet(s.durum) + '</div></div>'
            + '<div>' + (s.devralindi ? '<button class="wa-btn" data-geriver="' + s.id + '">Asistana geri ver</button>' : '<button class="wa-btn ikincil" data-devral="' + s.id + '">Devral (asistan sussun)</button>') + '</div></div>';
         if (s.gorseller.length) {
            h += '<div class="wa-gorseller">' + s.gorseller.map(function (g) {
               return '<figure>' + (g.dosya ? '<img loading="lazy" alt="Müşterinin gönderdiği model" src="' + url('/gorsel/' + g.id) + '">' : '<div class="wa-bos" style="padding:10px">Fotoğraf silindi</div>')
                  + '<figcaption>' + esc(g.tarif || '') + (g.toplam != null ? '<br><b>' + tl(g.toplam) + '</b>' : ' <span class="wa-rozet">' + esc(g.durum) + '</span>') + '</figcaption></figure>'; }).join('') + '</div>';
         }
         h += '<div class="wa-sohbet">' + s.satirlar.map(function (m) {
            var r = m.rol === 'musteri' ? 'Müşteri' : (m.rol === 'personel' ? 'Personel' : (m.geri_kazanim ? 'Asistan · geri kazanma' : (m.hatirlatma ? 'Hatırlatma' : 'Asistan')));
            return '<div class="wa-balon ' + m.rol + '"><span class="r">' + r + '</span>' + esc(m.metin) + '</div>'; }).join('') + '</div>';
         h += '<div class="wa-alt" style="margin-top:8px">Devraldığınızda asistan bu sohbette 24 saat ya da siz geri verene kadar cevap vermez. Telefonunuzdan müşteriye yazdığınızda da asistan otomatik susar.</div>';
         el('waSohbetDetay').innerHTML = h;
      }).catch(hata);
   }
   el('waSohbetDetay').addEventListener('click', function (e) {
      var b = e.target.closest('[data-devral],[data-geriver]'); if (!b) return;
      var id = b.dataset.devral || b.dataset.geriver; b.disabled = true;
      gonder('/sohbet/' + id + (b.dataset.devral ? '/devral' : '/geri-ver')).then(function (r) {
         toast(r.ok ? (b.dataset.devral ? 'Sohbeti devraldınız; asistan bu sohbette susacak.' : 'Sohbet asistana geri verildi.') : 'İşlem yapılamadı.');
         sohbetAc(id); sohbetListe();
      }).catch(hata);
   });

   // ---------- GERI KAZANMA
   var GK_DURUM = { gonderildi: ['Yazıldı', 'm'], cevap: ['Cevap verdi', 'm'], randevu: ['Randevu aldı', 'y'], geldi: ['Salona geldi', 'y'], gelmedi: ['Gelmedi', 'k'], ret: ['İstemedi', 'k'], gonderilemedi: ['Gönderilemedi', 'k'] };
   var GK_NEDEN = { fiyat: 'Fiyat aldı', pahali: 'Fiyatı yüksek buldu', dusunme: 'Düşünecekti', gorsel: 'Fotoğrafa fiyat aldı', saat: 'Saatlere baktı', bilgi: 'Bilgi aldı' };
   function gkYukle() {
      var kc = sonOzet ? sonOzet.kacan : [];
      el('waKacan').innerHTML = kc.length ? '<table class="wa-tablo"><thead><tr><th>Müşteri</th><th>Hizmet</th><th>Durum</th><th>Son mesaj</th></tr></thead><tbody>'
         + kc.map(function (x) { return '<tr><td>' + esc(x.ad || 'Müşteri') + '<div class="wa-alt">' + esc(x.telefon || 'gizli numara') + '</div></td><td>' + esc(x.hizmet || '—') + '</td><td>' + esc(x.neden) + (x.ret ? ' <span class="wa-rozet k">yazılmaz</span>' : '') + '</td><td>' + tarih(x.tarih) + '</td></tr>'; }).join('')
         + '</tbody></table>' : '<div class="wa-bos">Bu ay kaçan müşteri yok.</div>';
      el('waGkListe').innerHTML = '<div class="wa-bos">Yükleniyor…</div>';
      al('/geri-kazanim', { ay: aySec.value }).then(function (r) {
         if (!r.ok || !r.liste.length) { el('waGkListe').innerHTML = '<div class="wa-bos">Bu ay geri kazanma mesajı gönderilmedi.</div>'; return; }
         el('waGkListe').innerHTML = '<table class="wa-tablo"><thead><tr><th>Müşteri</th><th>Neden</th><th>Durum</th><th>Gönderildi</th><th>Randevu</th><th>Tutar</th></tr></thead><tbody>'
            + r.liste.map(function (x) { var d = GK_DURUM[x.durum] || [x.durum, ''];
               return '<tr title="' + esc(x.mesaj || '') + '"><td>' + esc(x.ad || 'Müşteri') + '<div class="wa-alt">' + esc(x.hizmet_ad || '') + '</div></td><td>' + esc(GK_NEDEN[x.neden] || x.neden || '') + '</td><td><span class="wa-rozet ' + d[1] + '">' + d[0] + '</span></td><td>' + tarih(x.gonderildi_at) + '</td><td>' + esc(x.randevu_tarih || '—') + '</td><td>' + (x.tutar ? tl(x.tutar) : '—') + '</td></tr>'; }).join('')
            + '</tbody></table>';
      }).catch(hata);
   }

   // ---------- FOTOGRAF FIYATLARI
   var sektorSec = el('waSektor');
   Object.keys(SEKTORLER).forEach(function (k) { sektorSec.insertAdjacentHTML('beforeend', '<option value="' + esc(k) + '">' + esc(SEKTORLER[k].ad) + '</option>'); });
   sektorSec.value = AYAR.gorsel_sektor || 'tirnak';
   el('waGorselNot').value = AYAR.gorsel_not || '';
   function hizmetSecim(secili) {
      return '<select class="wa-girdi" data-hizmet style="max-width:240px"><option value="">— hizmet seçin —</option>' + HIZMETLER.map(function (h) {
         return '<option value="' + h.id + '"' + (String(h.id) === String(secili || '') ? ' selected' : '') + '>' + esc(h.ad) + (Number(h.fiyat) > 0 ? ' (' + tl(h.fiyat) + ')' : '') + '</option>'; }).join('') + '</select>';
   }
   function fiyatGirdi(deger, ph) { return '<input type="number" min="0" step="0.01" class="wa-girdi" data-fiyat style="width:120px" placeholder="' + (ph || 'TL') + '" value="' + (deger != null ? esc(deger) : '') + '">'; }
   function kuralCiz() {
      var s = SEKTORLER[sektorSec.value], k = sektorSec.value === (AYAR.gorsel_sektor || 'tirnak') ? KURALLAR : {}, h = '';
      h += '<h4>Ana işlem</h4><table class="wa-tablo"><thead><tr><th>İşlem</th><th>Hizmetiniz (fiyatı listeden)</th><th>ya da sabit fiyat</th></tr></thead><tbody>';
      Object.keys(s.ana).forEach(function (a) { var r = k['ana:' + a] || {};
         h += '<tr data-anahtar="ana:' + a + '"><td>' + esc(s.ana[a]) + '</td><td>' + hizmetSecim(r.hizmet_id) + '</td><td>' + fiyatGirdi(r.fiyat) + '</td></tr>'; });
      h += '</tbody></table><h4 style="margin-top:16px">Ekler</h4><table class="wa-tablo"><thead><tr><th>Ek</th><th>Ek fiyat</th></tr></thead><tbody>';
      Object.keys(s.ozellikler).forEach(function (o) { var oz = s.ozellikler[o];
         if (oz.tip === 'bilgi') return;
         if (oz.tip === 'secim') Object.keys(oz.secenek).forEach(function (v) { var r = k[o + ':' + v] || {};
            h += '<tr data-anahtar="' + o + ':' + v + '"><td>' + esc(oz.ad) + ': ' + esc(oz.secenek[v]) + '</td><td>' + fiyatGirdi(r.fiyat, '+ TL') + '</td></tr>'; });
         else { var r = k[o] || {};
            h += '<tr data-anahtar="' + o + '"><td>' + esc(oz.ad) + (oz.tip === 'adet' ? ' <span class="wa-alt">(' + esc(oz.birim) + ' başı)</span>' : '') + '</td><td>' + fiyatGirdi(r.fiyat, oz.tip === 'adet' ? 'TL / ' + oz.birim : '+ TL') + '</td></tr>'; }
      });
      el('waKuralForm').innerHTML = h + '</tbody></table>';
   }
   sektorSec.addEventListener('change', kuralCiz); kuralCiz();
   el('waKuralKaydet').addEventListener('click', function () {
      var kur = {}, b = this; b.disabled = true;
      document.querySelectorAll('#waKuralForm tr[data-anahtar]').forEach(function (tr) {
         var hs = tr.querySelector('[data-hizmet]'), f = tr.querySelector('[data-fiyat]');
         var hid = hs ? hs.value : '', fy = f ? f.value : '';
         if (hid || fy !== '') kur[tr.dataset.anahtar] = { hizmet_id: hid || null, fiyat: fy !== '' ? fy : null };
      });
      Promise.all([gonder('/kurallar', { sektor: sektorSec.value, kurallar: kur }), gonder('/ayar', { gorsel_not: el('waGorselNot').value })]).then(function (r) {
         b.disabled = false;
         if (r[0].ok) { KURALLAR = r[0].kurallar; AYAR.gorsel_sektor = sektorSec.value; AYAR.gorsel_not = el('waGorselNot').value; toast('Fotoğraf fiyat kuralları kaydedildi.'); kuralCiz(); }
         else toast(r[0].hata || 'Kaydedilemedi.');
      }).catch(function () { b.disabled = false; hata(); });
   });

   // ---------- OGRENILEN
   function ogrYukle() {
      al('/ogrenilen').then(function (r) {
         if (!r.ok || !r.liste.length) { el('waOgrListe').innerHTML = '<div class="wa-bos">Henüz öğrenilmiş cevap yok. Asistan genel sorulara cevap verdikçe burası dolacak.</div>'; return; }
         el('waOgrListe').innerHTML = '<table class="wa-tablo"><thead><tr><th>Soru</th><th>Cevap</th><th>Ücretsiz kullanım</th><th></th></tr></thead><tbody>'
            + r.liste.map(function (x) { return '<tr><td>' + esc(x.soru) + '</td><td style="white-space:pre-wrap">' + esc(x.cevap) + '</td><td>' + sayi(x.adet) + '</td><td><button class="wa-btn ikincil" data-sil="' + x.id + '">Sil</button></td></tr>'; }).join('')
            + '</tbody></table>';
      }).catch(hata);
   }
   el('waOgrListe').addEventListener('click', function (e) {
      var b = e.target.closest('[data-sil]'); if (!b) return;
      if (!confirm('Bu öğrenilmiş cevap silinsin mi? Asistan bu soruya bir dahaki sefere yeniden cevap üretir.')) return;
      gonder('/ogrenilen/' + b.dataset.sil + '/sil').then(function (r) { toast(r.ok ? 'Silindi.' : 'Silinemedi.'); ogrYukle(); }).catch(hata);
   });

   // ---------- AYARLAR
   document.querySelectorAll('[data-ayar]').forEach(function (c) { c.checked = Number(AYAR[c.dataset.ayar]) === 1; });
   el('waBekle').value = AYAR.gk_bekle_saat || 24;
   el('waKampanya').value = AYAR.gk_kampanya || '';
   el('waAyarKaydet').addEventListener('click', function () {
      var v = { gk_bekle_saat: el('waBekle').value, gk_kampanya: el('waKampanya').value }, b = this; b.disabled = true;
      document.querySelectorAll('[data-ayar]').forEach(function (c) { v[c.dataset.ayar] = c.checked ? 1 : 0; });
      gonder('/ayar', v).then(function (r) { b.disabled = false; if (r.ok) { AYAR = r.ayar; toast('Ayarlar kaydedildi.'); } else toast('Kaydedilemedi.'); }).catch(function () { b.disabled = false; hata(); });
   });
})();
</script>
@endsection
