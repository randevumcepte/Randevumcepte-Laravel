{{-- Ayarlar > Bildirim Ayarlari (2026-11-22): SMS / WhatsApp / Uygulama bildirimi ayri ayri.
     Veri: /isletmeyonetim/bildirim-ayarlari/veri, kayit: /isletmeyonetim/bildirim-ayarlari/kaydet (her degisiklik loglanir). --}}
<style>
   .ba { padding:0 4px; }
   .ba-bas { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;
      padding:18px 20px; margin-bottom:18px; background:linear-gradient(135deg,#faf5ff 0%,#fff 100%);
      border:1px solid #ede1f7; border-left:4px solid #5C008E; border-radius:12px; }
   .ba-bas h2 { color:#5C008E; margin:0; font-size:22px; font-weight:700; }
   .ba-bas p { color:#6b7280; margin:4px 0 0; font-size:13px; max-width:720px; }
   .ba-bas a.ba-log { font-size:13px; font-weight:600; color:#5C008E; white-space:nowrap; }
   .ba-sekmeler { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px; }
   .ba-sekme { display:flex; align-items:center; gap:10px; padding:12px 18px; border-radius:12px; cursor:pointer;
      border:1px solid #e5e1ee; background:#fff; color:#3a2e57; font-weight:700; font-size:14px; transition:all .15s; user-select:none; }
   .ba-sekme i { font-size:18px; }
   .ba-sekme .ba-sayi { font-size:12px; font-weight:700; padding:2px 8px; border-radius:20px; background:#f1edf7; color:#5C008E; }
   .ba-sekme:hover { border-color:#c9b3e0; }
   .ba-sekme.aktif { background:#5C008E; border-color:#5C008E; color:#fff; box-shadow:0 6px 16px rgba(92,0,142,.18); }
   .ba-sekme.aktif .ba-sayi { background:rgba(255,255,255,.2); color:#fff; }
   .ba-durum { display:flex; align-items:flex-start; gap:12px; padding:14px 18px; border-radius:12px; margin-bottom:14px; font-size:13px; line-height:1.5; }
   .ba-durum i { font-size:20px; margin-top:1px; }
   .ba-durum b { display:block; font-size:14px; }
   .ba-durum.iyi { background:#ecfdf3; border:1px solid #bbf7d0; color:#166534; }
   .ba-durum.uyari { background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; }
   .ba-durum.bilgi { background:#f5f3ff; border:1px solid #ddd6fe; color:#4c1d95; }
   .ba-yedek { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:14px 18px; margin-bottom:14px;
      border:1px solid #ececf1; border-radius:12px; background:#fff; }
   .ba-yedek b { color:#2d2143; font-size:14px; }
   .ba-yedek small { display:block; color:#6b7280; font-size:12.5px; margin-top:2px; }
   .ba-arac { display:flex; gap:8px; flex-wrap:wrap; align-items:center; margin-bottom:6px; }
   .ba-arac span { font-size:12.5px; color:#6b7280; font-weight:600; margin-right:2px; }
   .ba-arac button { border:1px solid #e5e1ee; background:#fff; color:#3a2e57; border-radius:8px; padding:5px 10px; font-size:12.5px; font-weight:600; cursor:pointer; }
   .ba-arac button:hover { border-color:#5C008E; color:#5C008E; }
   .ba-grup { margin-top:18px; }
   .ba-grup h4 { font-size:15px; font-weight:700; color:#5C008E; margin:0 0 10px; display:flex; align-items:center; gap:8px; }
   .ba-grup h4 small { font-size:12px; color:#6b7280; font-weight:600; }
   .ba-izgara { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:12px; }
   .ba-kart { background:#fff; border:1px solid #ececf1; border-radius:12px; padding:14px 16px; display:flex; flex-direction:column; gap:10px;
      transition:box-shadow .15s,border-color .15s; }
   .ba-kart:hover { box-shadow:0 6px 18px rgba(92,0,142,.08); border-color:#ddd0ec; }
   .ba-kart.acik { border-color:#bbf7d0; }
   .ba-kart h6 { margin:0; font-weight:700; color:#2d2143; font-size:13.5px; }
   .ba-kart p { margin:0; color:#6b7280; font-size:12px; line-height:1.45; flex:1; }
   .ba-alicilar { display:flex; gap:8px; flex-wrap:wrap; }
   .ba-alici { display:flex; align-items:center; gap:8px; padding:6px 10px 6px 12px; border-radius:20px; cursor:pointer; user-select:none;
      border:1px solid #e5e7eb; background:#f9fafb; color:#6b7280; font-size:12.5px; font-weight:700; transition:all .15s; }
   .ba-alici.acik { background:#22C55E; border-color:#22C55E; color:#fff; }
   .ba-alici.bekle { opacity:.55; pointer-events:none; }
   .ba-alici .ba-kutu { width:18px; height:18px; border-radius:50%; background:#fff; border:1px solid #d1d5db; display:flex; align-items:center; justify-content:center; font-size:11px; color:#22C55E; }
   .ba-alici.acik .ba-kutu { border-color:#fff; }
   .ba-sw { position:relative; width:44px; height:24px; flex:0 0 44px; }
   .ba-sw input { opacity:0; width:0; height:0; }
   .ba-sw span { position:absolute; inset:0; background:#d1d5db; border-radius:24px; cursor:pointer; transition:.2s; }
   .ba-sw span:before { content:''; position:absolute; width:18px; height:18px; left:3px; top:3px; background:#fff; border-radius:50%; transition:.2s; }
   .ba-sw input:checked + span { background:#22C55E; }
   .ba-sw input:checked + span:before { transform:translateX(20px); }
   .ba-not { font-size:12px; color:#6b7280; margin-top:4px; }
   .ba-yuk { padding:40px; text-align:center; color:#6b7280; }
   .ba-toast { position:fixed; right:20px; bottom:20px; z-index:99999; padding:10px 16px; border-radius:10px; color:#fff; font-size:13px; font-weight:600;
      box-shadow:0 8px 24px rgba(0,0,0,.15); opacity:0; transform:translateY(10px); transition:all .2s; pointer-events:none; }
   .ba-toast.gor { opacity:1; transform:none; }
   @media (max-width:600px) { .ba-sekme { flex:1 1 100%; justify-content:space-between; } .ba-yedek { flex-direction:column; align-items:flex-start; } }
</style>

<div class="ba" id="baKok" data-sube="{{ $isletme->id }}">
   <div class="ba-bas">
      <div>
         <h2><i class="fa fa-bell"></i> Bildirim Ayarları</h2>
         <p>Her bildirimi SMS, WhatsApp ve uygulama bildirimi olarak ayrı ayrı açıp kapatabilirsiniz. Birden fazla kanal açıksa
            bildirim hepsinden birden gider; hiçbiri açık değilse gönderilmez. Yeşil olanlar gönderilir.</p>
      </div>
      <a class="ba-log" href="/isletmeyonetim/log-hareketleri?sube={{ $isletme->id }}&action=bildirim_ayar_degisti"><i class="fa fa-history"></i> Değişiklik geçmişi</a>
   </div>

   <div class="ba-sekmeler" id="baSekmeler">
      <div class="ba-sekme aktif" data-kanal="sms"><i class="fa fa-commenting-o"></i> SMS <span class="ba-sayi" data-sayi="sms">–</span></div>
      <div class="ba-sekme" data-kanal="whatsapp"><i class="fa fa-whatsapp"></i> WhatsApp <span class="ba-sayi" data-sayi="whatsapp">–</span></div>
      <div class="ba-sekme" data-kanal="push"><i class="fa fa-mobile"></i> Uygulama Bildirimi <span class="ba-sayi" data-sayi="push">–</span></div>
   </div>

   <div id="baIcerik"><div class="ba-yuk"><i class="fa fa-spinner fa-spin"></i> Yükleniyor…</div></div>
   <div class="ba-not"><i class="fa fa-lock"></i> Bu ayarları yalnızca işletme sahibi değiştirebilir. Her değişiklik, kimin yaptığıyla birlikte Log Hareketleri'ne kaydedilir.</div>
</div>
<div class="ba-toast" id="baToast"></div>

<script>
(function () {
   var kok = document.getElementById('baKok');
   if (!kok) return;
   var SUBE = kok.getAttribute('data-sube');
   var CSRF = '{{ csrf_token() }}';
   var KANAL = 'sms', VERI = null, yuklendi = false;
   var ALICI_AD = { musteri: 'Müşteriye', personel: 'Personele' };
   var KANAL_AD = { sms: 'SMS', whatsapp: 'WhatsApp', push: 'Uygulama bildirimi' };

   function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
   function toast(m, hata) {
      var t = document.getElementById('baToast');
      t.textContent = m; t.style.background = hata ? '#dc2626' : '#16a34a';
      t.classList.add('gor'); clearTimeout(t._z); t._z = setTimeout(function () { t.classList.remove('gor'); }, 2200);
   }
   function istek(yontem, yol, govde) {
      var o = { method: yontem, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, credentials: 'same-origin' };
      var u = '/isletmeyonetim/bildirim-ayarlari/' + yol;
      if (yontem === 'GET') { u += '?sube=' + encodeURIComponent(SUBE); }
      else {
         var fd = new FormData(); fd.append('_token', CSRF); fd.append('sube', SUBE);
         Object.keys(govde || {}).forEach(function (k) { if (govde[k] !== null && govde[k] !== undefined) fd.append(k, govde[k]); });
         o.body = fd;
      }
      return fetch(u, o).then(function (r) {
         return r.json().catch(function () { return { ok: 0, hata: 'Sunucu yanıtı okunamadı.' }; }).then(function (j) {
            if (!r.ok || !j.ok) throw new Error(j.hata || 'İşlem yapılamadı.');
            return j;
         });
      });
   }

   function sayilar() {
      var s = { sms: 0, whatsapp: 0, push: 0 };
      VERI.gruplar.forEach(function (g) { g.bildirimler.forEach(function (b) {
         Object.keys(b.alicilar).forEach(function (a) { ['sms', 'whatsapp', 'push'].forEach(function (k) { if (+b.alicilar[a][k] === 1) s[k]++; }); });
      }); });
      Object.keys(s).forEach(function (k) { var e = kok.querySelector('[data-sayi="' + k + '"]'); if (e) e.textContent = s[k] + ' açık'; });
   }

   function durumKart() {
      var d = VERI.durum || {}, h = '';
      if (KANAL === 'sms') {
         if (!d.sms_hesabi) h = '<div class="ba-durum uyari"><i class="fa fa-exclamation-triangle"></i><div><b>SMS hesabınız tanımlı değil</b>SMS açık olsa da gönderilemez. SMS paketi için bizimle iletişime geçin.</div></div>';
         else if (d.sms_bakiye === 0) h = '<div class="ba-durum uyari"><i class="fa fa-exclamation-triangle"></i><div><b>SMS bakiyeniz bitti</b>SMS paketi yüklenene kadar SMS gönderilmez; WhatsApp ulaşmadığında yedek SMS de gitmez.</div></div>';
         else h = '<div class="ba-durum iyi"><i class="fa fa-check-circle"></i><div><b>SMS hesabınız aktif' + (d.sms_bakiye != null ? ' · Kalan: ' + Number(d.sms_bakiye).toLocaleString('tr-TR') + ' SMS' : '') + '</b>SMS kapalı olan bir bildirim, WhatsApp ulaşamazsa yedek olarak yine SMS ile gidebilir (WhatsApp sekmesindeki ayar).</div></div>';
      } else if (KANAL === 'whatsapp') {
         h = d.wa_bagli
            ? '<div class="ba-durum iyi"><i class="fa fa-whatsapp"></i><div><b>WhatsApp bağlı</b>Açık olan bildirimler WhatsApp\'tan gider.</div></div>'
            : '<div class="ba-durum uyari"><i class="fa fa-whatsapp"></i><div><b>WhatsApp bağlı değil</b>WhatsApp\'ı bağlayana kadar WhatsApp\'tan mesaj gitmez. Aşağıdaki yedek ayarı açıksa bu bildirimler SMS ile gider.</div></div>';
         h += '<div class="ba-yedek"><div><b>WhatsApp ulaşmazsa SMS gönder</b><small>WhatsApp bağlı değilse veya mesaj iletilemezse aynı mesaj SMS ile gider. SMS paketiniz yoksa SMS de gitmez.</small></div>'
            + '<label class="ba-sw"><input type="checkbox" id="baYedek"' + (VERI.wa_yedek_sms ? ' checked' : '') + '><span></span></label></div>';
      } else {
         h = '<div class="ba-durum bilgi"><i class="fa fa-mobile"></i><div><b>Uygulamayı kullanan müşteri: ' + (+d.uygulama_musteri || 0) + '</b>Uygulama bildirimi ücretsizdir; yalnızca uygulamanızı yüklemiş müşteriye ve personele ulaşır.</div></div>';
      }
      return h;
   }

   function ciz() {
      sayilar();
      var h = durumKart();
      h += '<div class="ba-arac"><span>' + KANAL_AD[KANAL] + ':</span>'
         + '<button type="button" data-toplu="1" data-alici="musteri">Müşteriye hepsini aç</button>'
         + '<button type="button" data-toplu="0" data-alici="musteri">Müşteriye hepsini kapat</button>'
         + '<button type="button" data-toplu="1" data-alici="personel">Personele hepsini aç</button>'
         + '<button type="button" data-toplu="0" data-alici="personel">Personele hepsini kapat</button></div>';
      VERI.gruplar.forEach(function (g) {
         h += '<div class="ba-grup"><h4>' + esc(g.ad) + (g.kod === 'guvenlik' ? ' <small>· üç kanalda da kapalıysa kod hiç istenmez</small>' : '') + '</h4><div class="ba-izgara">';
         g.bildirimler.forEach(function (b) {
            var acikVar = false, al = '';
            Object.keys(b.alicilar).forEach(function (a) {
               var acik = +b.alicilar[a][KANAL] === 1; if (acik) acikVar = true;
               al += '<div class="ba-alici' + (acik ? ' acik' : '') + '" data-kod="' + esc(b.kod) + '" data-alici="' + a + '" title="' + (acik ? 'Kapatmak' : 'Açmak') + ' için tıklayın">'
                  + '<span class="ba-kutu">' + (acik ? '<i class="fa fa-check"></i>' : '') + '</span>' + ALICI_AD[a] + '</div>';
            });
            h += '<div class="ba-kart' + (acikVar ? ' acik' : '') + '"><h6>' + esc(b.ad) + '</h6><p>' + esc(b.aciklama) + '</p><div class="ba-alicilar">' + al + '</div></div>';
         });
         h += '</div></div>';
      });
      document.getElementById('baIcerik').innerHTML = h;
   }

   function yukle() {
      if (yuklendi) return;
      yuklendi = true;
      istek('GET', 'veri').then(function (j) { VERI = j; ciz(); }).catch(function (e) {
         yuklendi = false;
         document.getElementById('baIcerik').innerHTML = '<div class="ba-durum uyari"><i class="fa fa-exclamation-triangle"></i><div><b>Ayarlar yüklenemedi</b>' + esc(e.message) + '</div></div>';
      });
   }

   kok.addEventListener('click', function (ev) {
      var sekme = ev.target.closest('.ba-sekme');
      if (sekme) {
         KANAL = sekme.getAttribute('data-kanal');
         kok.querySelectorAll('.ba-sekme').forEach(function (x) { x.classList.toggle('aktif', x === sekme); });
         if (VERI) ciz();
         return;
      }
      var p = ev.target.closest('.ba-alici');
      if (p && VERI) {
         var kod = p.getAttribute('data-kod'), alici = p.getAttribute('data-alici');
         var b = null; VERI.gruplar.forEach(function (g) { g.bildirimler.forEach(function (x) { if (x.kod === kod) b = x; }); });
         if (!b) return;
         var yeni = +b.alicilar[alici][KANAL] === 1 ? 0 : 1, kanal = KANAL;
         b.alicilar[alici][kanal] = yeni; p.classList.add('bekle'); ciz();
         istek('POST', 'kaydet', { kod: kod, alici: alici, kanal: kanal, deger: yeni }).then(function (j) {
            b.alicilar[alici] = { sms: j.kanallar.sms ? 1 : 0, whatsapp: j.kanallar.whatsapp ? 1 : 0, push: j.kanallar.push ? 1 : 0 };
            if (kanal === KANAL) ciz(); else sayilar();
            toast(b.ad + ' · ' + ALICI_AD[alici] + ' · ' + KANAL_AD[kanal] + (yeni ? ' açıldı' : ' kapatıldı'));
         }).catch(function (e) {
            b.alicilar[alici][kanal] = yeni ? 0 : 1; ciz(); toast(e.message, true);
         });
         return;
      }
      var t = ev.target.closest('[data-toplu]');
      if (t && VERI) {
         var ac = t.getAttribute('data-toplu') === '1', a = t.getAttribute('data-alici');
         if (!confirm(KANAL_AD[KANAL] + ' · ' + ALICI_AD[a] + ' giden tüm bildirimler ' + (ac ? 'AÇILACAK' : 'KAPATILACAK') + '. Emin misiniz?')) return;
         t.disabled = true;
         istek('POST', 'kaydet', { toplu: 1, kanal: KANAL, alici: a, deger: ac ? 1 : 0 }).then(function (j) {
            VERI = j; ciz(); toast(j.degisen + ' bildirim ' + (ac ? 'açıldı' : 'kapatıldı'));
         }).catch(function (e) { t.disabled = false; toast(e.message, true); });
      }
   });
   kok.addEventListener('change', function (ev) {
      if (ev.target.id !== 'baYedek') return;
      var el = ev.target, yeni = el.checked ? 1 : 0;
      el.disabled = true;
      istek('POST', 'kaydet', { wa_yedek_sms: yeni }).then(function (j) {
         VERI.wa_yedek_sms = !!j.wa_yedek_sms; el.disabled = false;
         toast('WhatsApp ulaşmazsa SMS gönder: ' + (yeni ? 'açıldı' : 'kapatıldı'));
      }).catch(function (e) { el.checked = !yeni; el.disabled = false; toast(e.message, true); });
   });

   // Sekme acildiginda (veya dogrudan ?p=bildirimayarlari ile gelindiginde) yukle.
   // jQuery bu panelden SONRA yuklenebilir: tiklamayi duz JS ile yakala, jQuery olayina sayfa yuklenince baglan.
   document.addEventListener('click', function (ev) {
      if (ev.target.closest && ev.target.closest('a[href="#bildirim-ayarlari"]')) setTimeout(yukle, 60);
   });
   window.addEventListener('load', function () {
      if (window.jQuery) jQuery(document).on('shown.bs.tab', 'a[href="#bildirim-ayarlari"]', yukle);
      var pane = document.getElementById('bildirim-ayarlari');
      if (location.search.indexOf('p=bildirimayarlari') !== -1 || (pane && pane.classList.contains('active'))) yukle();
   });
})();
</script>
