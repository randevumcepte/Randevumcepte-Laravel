{{-- Ayarlar > Bildirim Ayarlari (2026-11-22): Bildirim Yonetim Merkezi. Satir = bildirim, sutun = alici
     (Musteri / Personel), her alicida ana anahtar + SMS / WhatsApp / Push cipleri. Degisiklikler "Kaydet" ile toplu gider.
     Veri: /isletmeyonetim/bildirim-ayarlari/veri, kayit: /isletmeyonetim/bildirim-ayarlari/kaydet (her degisiklik loglanir). --}}
<style>
   .bm { --bm-mor:#5C008E; --bm-mor2:#7B2FB8; --bm-acik:#f6f0fc; --bm-cizgi:#ece6f3; --bm-yazi:#1f1633; --bm-gri:#6b7280;
      --bm-yesil:#16a34a; --bm-wa:#22C55E; color:var(--bm-yazi); }
   .bm-bas { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:18px; }
   .bm-bas-sol { display:flex; align-items:center; gap:16px; }
   .bm-ikon { width:56px; height:56px; border-radius:14px; background:linear-gradient(135deg,var(--bm-mor),var(--bm-mor2)); color:#fff;
      display:flex; align-items:center; justify-content:center; font-size:26px; box-shadow:0 8px 20px rgba(92,0,142,.25); flex:0 0 56px; }
   .bm-bas h2 { margin:0; font-size:24px; font-weight:700; color:var(--bm-yazi); letter-spacing:-.2px; }
   .bm-bas p { margin:2px 0 0; color:var(--bm-gri); font-size:14px; }
   .bm-bas-sag { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
   .bm-gecmis { font-size:13px; font-weight:600; color:var(--bm-mor); }
   .bm-kaydet { border:0; border-radius:10px; padding:11px 18px; font-weight:700; font-size:14px; color:#fff; cursor:pointer;
      background:linear-gradient(135deg,var(--bm-mor),var(--bm-mor2)); box-shadow:0 6px 16px rgba(92,0,142,.25); display:flex; align-items:center; gap:8px; }
   /* Degisiklik yokken: yesil 'Kaydedildi' (tiklanmaz); degisiklik varken mor + sayac; kaydederken bekleme */
   /* Sag altta sabit: sayfa kaydirilsa da hep gorunur (sekme gizliyken o da gizli) */
   .bm-kaydet.bm-yuzen { position:fixed; right:28px; bottom:26px; z-index:1040; padding:14px 22px; font-size:15px; border-radius:14px;
      box-shadow:0 12px 28px rgba(92,0,142,.35); }
   .bm-kaydet.bm-yuzen.bekliyor { animation:bmNabiz 1.6s ease-in-out infinite; }
   @keyframes bmNabiz { 0%,100% { box-shadow:0 12px 28px rgba(92,0,142,.35); } 50% { box-shadow:0 12px 34px rgba(245,158,11,.55); } }
   .bm-modal-arka { position:fixed; inset:0; z-index:100000; background:rgba(15,23,42,.45); display:flex; align-items:center; justify-content:center; padding:16px; }
   .bm-modal { background:#fff; border-radius:16px; padding:28px 28px 22px; width:100%; max-width:380px; text-align:center; box-shadow:0 24px 60px rgba(15,23,42,.25); }
   .bm-modal .bm-modal-ikon { width:64px; height:64px; border-radius:50%; margin:0 auto 14px; background:#e9f9ef; color:#16a34a; display:flex; align-items:center; justify-content:center; font-size:32px; }
   .bm-modal h3 { margin:0 0 6px; font-size:19px; font-weight:700; color:#1f1633; }
   .bm-modal p { margin:0 0 18px; color:#6b7280; font-size:14px; line-height:1.45; }
   .bm-modal button { border:0; border-radius:10px; padding:11px 30px; font-weight:700; font-size:14px; color:#fff; cursor:pointer; background:linear-gradient(135deg,#5C008E,#7B2FB8); }
   .bm-kaydet.bekle { opacity:.7; cursor:wait; }
   .bm-kaydet .bm-rozet { background:#fff; color:var(--bm-mor); border-radius:20px; font-size:11.5px; padding:1px 7px; }

   .bm-ust { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)) minmax(0,1.25fr); gap:14px; margin-bottom:18px; }
   .bm-kart { background:#fff; border:1px solid var(--bm-cizgi); border-radius:14px; box-shadow:0 2px 8px rgba(31,22,51,.04); }
   .bm-durum { display:flex; align-items:center; gap:14px; padding:16px; position:relative; }
   .bm-durum-ikon { width:52px; height:52px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:24px; flex:0 0 52px; }
   .bm-durum b { display:block; font-size:15.5px; font-weight:700; }
   .bm-durum small { display:block; color:var(--bm-gri); font-size:12.5px; margin-top:2px; line-height:1.35; }
   .bm-kalan { margin-top:6px; font-size:13px; color:var(--bm-gri); }
   .bm-kalan strong { font-size:18px; font-weight:700; margin:0 2px; }
   .bm-durum .bm-tik { position:absolute; top:14px; right:14px; width:24px; height:24px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-size:12px; }
   .bm-tik.iyi { background:var(--bm-wa); } .bm-tik.kotu { background:#f59e0b; }
   .bm-sayilar { display:flex; align-items:center; justify-content:space-around; padding:12px 8px; }
   .bm-sayi { text-align:center; flex:1; }
   .bm-sayi + .bm-sayi { border-left:1px solid var(--bm-cizgi); }
   .bm-sayi b { display:block; font-size:28px; font-weight:700; color:var(--bm-yazi); line-height:1.1; }
   .bm-sayi span { font-size:12.5px; color:var(--bm-gri); }

   .bm-tablo { overflow:hidden; margin-bottom:18px; }
   .bm-satir { display:grid; grid-template-columns:minmax(0,1.25fr) minmax(0,1fr) minmax(0,1fr) minmax(0,1fr); align-items:center; gap:12px;
      padding:11px 18px; border-top:1px solid #f2eef7; }
   .bm-satir.bm-th { background:var(--bm-acik); border-top:0; font-weight:700; font-size:14px; color:var(--bm-yazi); padding:13px 18px; }
   .bm-satir.bm-th small { display:block; font-weight:500; color:var(--bm-gri); font-size:11.5px; }
   .bm-grup { padding:10px 18px; background:#fbf9fe; border-top:1px solid #f2eef7; font-size:12px; font-weight:700; letter-spacing:.5px;
      text-transform:uppercase; color:var(--bm-mor); }
   .bm-ad { display:flex; align-items:center; gap:12px; min-width:0; }
   .bm-ad i.bm-ri { width:30px; text-align:center; font-size:18px; flex:0 0 30px; }
   .bm-ad-yazi { min-width:0; }
   .bm-ad-yazi b { display:block; font-size:14.5px; font-weight:600; color:var(--bm-yazi); }
   .bm-ad-yazi small { display:block; font-size:12px; color:var(--bm-gri); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
   .bm-saat { margin-top:5px; border:1px solid #ddd6e8; border-radius:8px; padding:3px 8px; font-size:12.5px; background:#fff; color:var(--bm-yazi); }
   .bm-alici { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
   .bm-alici.bm-yok { color:#c4bdd0; font-size:13px; }
   .bm-alici .bm-etiket { display:none; font-size:12px; font-weight:700; color:var(--bm-gri); width:100%; }

   .bm-sw { position:relative; width:44px; height:24px; flex:0 0 44px; margin:0; cursor:pointer; }
   .bm-sw input { opacity:0; width:0; height:0; position:absolute; }
   .bm-sw span { position:absolute; inset:0; background:#d9d4e2; border-radius:24px; transition:.2s; }
   .bm-sw span:before { content:''; position:absolute; width:18px; height:18px; left:3px; top:3px; background:#fff; border-radius:50%; transition:.2s; box-shadow:0 1px 3px rgba(0,0,0,.2); }
   .bm-sw input:checked + span { background:linear-gradient(135deg,var(--bm-mor),var(--bm-mor2)); }
   .bm-sw input:checked + span:before { transform:translateX(20px); }
   .bm-sw.yesil input:checked + span { background:var(--bm-wa); }

   .bm-cip { display:inline-flex; align-items:center; gap:6px; border-radius:20px; padding:4px 11px 4px 9px; font-size:12.5px; font-weight:600;
      border:1px solid #e3dfea; background:#fff; color:#9a93a8; cursor:pointer; user-select:none; transition:all .15s; position:relative; }
   .bm-cip i { font-size:13px; }
   .bm-cip:hover { border-color:#c9b3e0; }
   .bm-cip.acik[data-kanal="sms"] { background:#f3ebfb; border-color:#dcc6f1; color:var(--bm-mor); }
   .bm-cip.acik[data-kanal="whatsapp"] { background:#e9f9ef; border-color:#bdeccd; color:#15803d; }
   .bm-cip.acik[data-kanal="push"] { background:#efeafe; border-color:#d4c8fb; color:#6d28d9; }
   .bm-cip.degisti:after { content:''; position:absolute; top:-2px; right:-2px; width:8px; height:8px; border-radius:50%; background:#f59e0b; border:2px solid #fff; }

   .bm-alt { display:grid; grid-template-columns:1fr 1.35fr; gap:14px; }
   .bm-alt .bm-kart { padding:18px 20px; }
   .bm-alt h4 { margin:0; font-size:17px; font-weight:700; display:flex; align-items:center; gap:10px; }
   .bm-alt h4 i { color:var(--bm-mor); font-size:22px; }
   .bm-alt p { color:var(--bm-gri); font-size:13px; margin:4px 0 0; }
   .bm-yakinda { display:inline-block; font-size:11px; font-weight:700; color:#9a3412; background:#ffedd5; border-radius:20px; padding:2px 9px; margin-left:6px; vertical-align:middle; }
   .bm-kilit { margin-top:14px; padding:11px 14px; border-radius:10px; background:var(--bm-acik); color:var(--bm-mor); font-size:13px; font-weight:600; text-align:center; }
   .bm-kural { display:flex; align-items:flex-start; gap:12px; margin-top:14px; }
   .bm-kural b { display:block; font-size:14px; font-weight:600; }
   .bm-kural small { color:var(--bm-gri); font-size:12.5px; }
   .bm-kurallar { display:grid; grid-template-columns:1fr minmax(0,.8fr); gap:16px; align-items:start; }
   .bm-bilgi { display:flex; gap:10px; padding:14px; border-radius:12px; background:var(--bm-acik); color:#4c1d95; font-size:13px; line-height:1.45; }
   .bm-bilgi i { font-size:18px; color:var(--bm-mor); margin-top:1px; }
   .bm-not { font-size:12px; color:var(--bm-gri); margin-top:12px; }
   .bm-yuk { padding:50px; text-align:center; color:var(--bm-gri); }
   .bm-hata { padding:16px; border-radius:12px; background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; }
   .bm-toast { position:fixed; right:20px; bottom:20px; z-index:99999; padding:11px 16px; border-radius:10px; color:#fff; font-size:13px; font-weight:600;
      box-shadow:0 8px 24px rgba(0,0,0,.15); opacity:0; transform:translateY(10px); transition:all .2s; pointer-events:none; }
   .bm-toast.gor { opacity:1; transform:none; }

   @media (min-width:992px) and (max-width:1560px) { .bm-cip-ad { display:none; } .bm-cip { padding:5px 9px; } .bm-cip i { font-size:14px; } }
   @media (max-width:1300px) { .bm-ust { grid-template-columns:repeat(3,minmax(0,1fr)); } .bm-sayilar { grid-column:1 / -1; } }
   @media (max-width:991px) {
      .bm-ust { grid-template-columns:1fr; }
      .bm-satir { grid-template-columns:1fr; gap:10px; }
      .bm-satir.bm-th { display:none; }
      .bm-alici .bm-etiket { display:block; }
      .bm-alt, .bm-kurallar { grid-template-columns:1fr; }
   }
</style>

<div class="bm" id="bmKok" data-sube="{{ $isletme->id }}">
   <div class="bm-bas">
      <div class="bm-bas-sol">
         <div class="bm-ikon"><i class="fa fa-bell-o"></i></div>
         <div>
            <h2>Bildirim Yönetim Merkezi</h2>
            <p>Müşteri ve ekip bildirimlerini tek yerden yönetin. Her bildirim için SMS, WhatsApp ve uygulama bildirimini ayrı ayrı seçin.</p>
         </div>
      </div>
      <div class="bm-bas-sag">
         <a class="bm-gecmis" href="/isletmeyonetim/log-hareketleri?sube={{ $isletme->id }}&action=bildirim_ayar_degisti"><i class="fa fa-history"></i> Değişiklik geçmişi</a>
      </div>
   </div>
   <div id="bmIcerik"><div class="bm-yuk"><i class="fa fa-spinner fa-spin"></i> Yükleniyor…</div></div>
   <button type="button" class="bm-kaydet bm-yuzen" id="bmKaydet"><i class="fa fa-floppy-o"></i> <span id="bmKaydetYazi">Değişiklikleri Kaydet</span> <span class="bm-rozet" id="bmSayac" style="display:none"></span></button>
</div>
<div class="bm-toast" id="bmToast"></div>

<script>
(function () {
   var kok = document.getElementById('bmKok');
   if (!kok) return;
   var SUBE = kok.getAttribute('data-sube');
   var CSRF = '{{ csrf_token() }}';
   var KANALLAR = ['sms', 'whatsapp', 'push'];
   var KANAL_AD = { sms: 'SMS', whatsapp: 'WhatsApp', push: 'Uygulama' };
   var KANAL_IKON = { sms: 'fa-commenting', whatsapp: 'fa-whatsapp', push: 'fa-bell' };
   var IKON = {
      randevu_olusturuldu: ['fa-calendar-plus-o', '#7c3aed'], randevu_guncellendi: ['fa-exchange', '#2563eb'],
      randevu_onaylandi: ['fa-check-circle-o', '#16a34a'], randevu_reddedildi: ['fa-times-circle-o', '#dc2626'],
      randevu_iptal: ['fa-ban', '#dc2626'], online_randevu_talebi: ['fa-laptop', '#2563eb'], musteri_geldi: ['fa-sign-in', '#0891b2'],
      hatirlatma_yaklasan: ['fa-bell-o', '#7c3aed'], hatirlatma_bir_gun_once: ['fa-clock-o', '#f59e0b'],
      seans_hatirlatma: ['fa-hourglass-half', '#7c3aed'], ders_hatirlatma: ['fa-users', '#0d9488'], ders_katilim_sor: ['fa-question-circle-o', '#0d9488'],
      alacak_hatirlatma: ['fa-money', '#16a34a'], senet_vade: ['fa-file-text-o', '#b45309'], tekrar_arama: ['fa-phone', '#2563eb'],
      musteri_eklendi: ['fa-user-plus', '#16a34a'], dogum_gunu: ['fa-birthday-cake', '#db2777'], kara_liste: ['fa-user-times', '#475569'],
      degerlendirme_anketi: ['fa-star-o', '#f59e0b'], kotu_degerlendirme: ['fa-thumbs-o-down', '#dc2626'], musteri_foto: ['fa-picture-o', '#0891b2'],
      damga_karti: ['fa-ticket', '#7c3aed'], cark_hatirlatma: ['fa-life-ring', '#db2777'], seans_kullanim: ['fa-check-square-o', '#7c3aed'],
      para_islemleri: ['fa-money', '#16a34a'], dogrulama_kodu: ['fa-key', '#475569'], kvkk_onay: ['fa-shield', '#475569'],
      silme_onay_kodu: ['fa-lock', '#dc2626'], kampanya_link: ['fa-bullhorn', '#f59e0b'], wa_asistan_talep: ['fa-whatsapp', '#16a34a'],
      telefon_asistani: ['fa-headphones', '#2563eb']
   };
   var VERI = null, ORJ = null, yuklendi = false, sonKayit = '';

   function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
   function kopya(o) { return JSON.parse(JSON.stringify(o)); }
   function toast(m, hata) {
      var t = document.getElementById('bmToast');
      t.textContent = m; t.style.background = hata ? '#dc2626' : '#16a34a';
      t.classList.add('gor'); clearTimeout(t._z); t._z = setTimeout(function () { t.classList.remove('gor'); }, 2600);
   }
   function istek(yontem, yol, govde) {
      var o = { method: yontem, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, credentials: 'same-origin' };
      var u = '/isletmeyonetim/bildirim-ayarlari/' + yol;
      if (yontem === 'GET') u += '?sube=' + encodeURIComponent(SUBE);
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
   function bildirimBul(v, kod) { var b = null; v.gruplar.forEach(function (g) { g.bildirimler.forEach(function (x) { if (x.kod === kod) b = x; }); }); return b; }

   // Kaydedilmemis farklar
   function farklar() {
      var l = [];
      VERI.gruplar.forEach(function (g) { g.bildirimler.forEach(function (b) {
         var o = bildirimBul(ORJ, b.kod);
         Object.keys(b.alicilar).forEach(function (a) { KANALLAR.forEach(function (k) {
            if (+b.alicilar[a][k] !== +o.alicilar[a][k]) l.push({ kod: b.kod, alici: a, kanal: k, deger: +b.alicilar[a][k] });
         }); });
      }); });
      return l;
   }
   function digerFark() {
      var d = {};
      if (!!VERI.wa_yedek_sms !== !!ORJ.wa_yedek_sms) d.wa_yedek_sms = VERI.wa_yedek_sms ? 1 : 0;
      if (+VERI.hatirlatma_saat !== +ORJ.hatirlatma_saat) d.hatirlatma_saat = +VERI.hatirlatma_saat;
      return d;
   }
   function sayacGuncelle() {
      var n = farklar().length + Object.keys(digerFark()).length;
      var btn = document.getElementById('bmKaydet'), s = document.getElementById('bmSayac');
      s.style.display = n ? '' : 'none'; s.textContent = n;
      btn.classList.toggle('bekliyor', n > 0);
      if (!btn.classList.contains('bekle')) document.getElementById('bmKaydetYazi').textContent = 'Değişiklikleri Kaydet';
   }

   function durumKartlari() {
      var d = VERI.durum || {}, smsIyi = d.sms_hesabi && d.sms_bakiye !== 0, toplam = 0;
      VERI.gruplar.forEach(function (g) { toplam += g.bildirimler.length; });
      var smsAlt = !d.sms_hesabi ? 'SMS hesabı tanımlı değil; SMS gönderilemez.'
         : (d.sms_bakiye === 0 ? 'SMS paketiniz bitti; SMS gönderilemez.' : 'SMS bildirimleri gönderilir.');
      function sayi(n) { return Number(n || 0).toLocaleString('tr-TR'); }
      // Kalan: SMS hesabi yoksa 0; bakiye sorgulanamadiysa '—'
      var smsKalan = !d.sms_hesabi ? sayi(0) : (d.sms_bakiye == null ? '—' : sayi(d.sms_bakiye));
      function kart(ikon, renk, zemin, baslik, alt, iyi, kalan, birim) {
         // Hafif renkli zemin + sol renk seridi; sorunlu kart turuncu
         var vurgu = iyi ? renk : '#f59e0b', acik = iyi ? zemin : '#fff7ed';
         return '<div class="bm-kart bm-durum" style="background:linear-gradient(135deg,' + acik + ' 0%,#fff 78%);border-color:' + acik + ';border-left:4px solid ' + vurgu + '"><div class="bm-durum-ikon" style="background:#fff;box-shadow:0 2px 8px rgba(31,22,51,.06);color:' + renk + '"><i class="fa ' + ikon + '"></i></div>'
            + '<div><b>' + baslik + '</b><small>' + esc(alt) + '</small>'
            + (kalan != null ? '<div class="bm-kalan">Kalan <strong style="color:' + renk + '">' + esc(kalan) + '</strong> ' + birim + '</div>' : '') + '</div><span class="bm-tik ' + (iyi ? 'iyi' : 'kotu') + '"><i class="fa ' + (iyi ? 'fa-check' : 'fa-exclamation') + '"></i></span></div>';
      }
      return '<div class="bm-ust">'
         + kart('fa-commenting', '#5C008E', '#f3ebfb', smsIyi ? 'SMS Aktif' : 'SMS Kullanılamıyor', smsAlt, smsIyi, smsKalan, 'SMS')
         + kart('fa-whatsapp', '#16a34a', '#e9f9ef', d.wa_bagli ? 'WhatsApp Bağlı' : 'WhatsApp Bağlı Değil', d.wa_bagli ? 'WhatsApp bildirimleri gönderilir.' : 'Bağlayana kadar WhatsApp\'tan mesaj gitmez.', d.wa_bagli, sayi(d.wa_kontor), 'kontör')
         + kart('fa-bell', '#6d28d9', '#efeafe', 'Uygulama Bildirimi Aktif', 'Uygulamayı kullanan müşteri: ' + (+d.uygulama_musteri || 0), true)
         + '<div class="bm-kart bm-sayilar" style="background:linear-gradient(135deg,#f6f0fc 0%,#fff 80%);border-color:#ece0f8;border-left:4px solid #5C008E"><div class="bm-sayi"><b>' + toplam + '</b><span>Bildirim türü</span></div>'
         + '<div class="bm-sayi"><b>3</b><span>Kanal</span></div><div class="bm-sayi"><b>' + (+d.personel_sayisi || 0) + '</b><span>Personel</span></div></div>'
         + '</div>';
   }

   function aliciHucre(b, a, etiket) {
      var r = b.alicilar[a];
      if (!r) return '<div class="bm-alici bm-yok"><span class="bm-etiket">' + etiket + '</span>—</div>';
      var o = bildirimBul(ORJ, b.kod).alicilar[a], acik = +r.sms || +r.whatsapp || +r.push;
      var h = '<div class="bm-alici"><span class="bm-etiket">' + etiket + '</span>'
         + '<label class="bm-sw" title="' + etiket + ': hepsini aç/kapat"><input type="checkbox" data-ana="1" data-kod="' + esc(b.kod) + '" data-alici="' + a + '"' + (acik ? ' checked' : '') + '><span></span></label>';
      KANALLAR.forEach(function (k) {
         h += '<span class="bm-cip' + (+r[k] ? ' acik' : '') + (+r[k] !== +o[k] ? ' degisti' : '') + '" data-kod="' + esc(b.kod) + '" data-alici="' + a + '" data-kanal="' + k
            + '" title="' + KANAL_AD[k] + ' ' + (+r[k] ? 'açık — kapatmak için tıklayın' : 'kapalı — açmak için tıklayın') + '"><i class="fa ' + KANAL_IKON[k] + '"></i><span class="bm-cip-ad">' + KANAL_AD[k] + '</span></span>';
      });
      return h + '</div>';
   }

   function ciz() {
      var h = durumKartlari();
      h += '<div class="bm-kart bm-tablo"><div class="bm-satir bm-th"><div>Bildirim Türü</div><div>Müşteri<small>Ana anahtar + gönderim kanalları</small></div><div>Personel<small>Randevuyu alan / ilgili çalışan</small></div><div>Yönetici<small>İşletme sahibi ve yöneticiler</small></div></div>';
      VERI.gruplar.forEach(function (g) {
         h += '<div class="bm-grup">' + esc(g.ad) + (g.kod === 'guvenlik' ? ' · üç kanalda da kapalıysa kod hiç istenmez' : '') + '</div>';
         g.bildirimler.forEach(function (b) {
            var ik = IKON[b.kod] || ['fa-bell-o', '#7c3aed'];
            var saat = '';
            if (b.kod === 'hatirlatma_yaklasan') {
               saat = '<select class="bm-saat" id="bmSaat">';
               for (var i = 1; i <= 23; i++) saat += '<option value="' + i + '"' + (+VERI.hatirlatma_saat === i ? ' selected' : '') + '>' + i + ' saat önce</option>';
               saat += '</select>';
            }
            h += '<div class="bm-satir"><div class="bm-ad"><i class="fa ' + ik[0] + ' bm-ri" style="color:' + ik[1] + '"></i>'
               + '<div class="bm-ad-yazi"><b>' + esc(b.ad) + '</b><small title="' + esc(b.aciklama) + '">' + esc(b.aciklama) + '</small>' + saat + '</div></div>'
               + aliciHucre(b, 'musteri', 'Müşteri') + aliciHucre(b, 'personel', 'Personel') + aliciHucre(b, 'yonetici', 'Yönetici') + '</div>';
         });
      });
      h += '</div>';

      h += '<div class="bm-alt">'
         + '<div class="bm-kart"><h4><i class="fa fa-user-circle-o"></i> Personel Bildirim Tercihleri <span class="bm-yakinda">Yakında</span></h4>'
         + '<p>Personel, kendisine gelen bildirimleri uygulamada sessize alabilecek. Bildirim yine gelir, yalnızca ses ve titreşim olmaz. Burada kapattığınız bildirimi personel açamaz.</p>'
         + '<div class="bm-kilit"><i class="fa fa-lock"></i> Hangi bildirimin gideceğine işletme sahibi karar verir</div></div>'
         + '<div class="bm-kart"><h4><i class="fa fa-magic"></i> Akıllı Bildirim Kuralları</h4><div class="bm-kurallar"><div>'
         + '<div class="bm-kural"><label class="bm-sw yesil"><input type="checkbox" id="bmYedek"' + (VERI.wa_yedek_sms ? ' checked' : '') + '><span></span></label>'
         + '<div><b>WhatsApp başarısız olursa SMS gönder</b><small>WhatsApp bağlı değilse veya mesaj iletilemezse aynı mesaj SMS ile gider. SMS paketiniz yoksa SMS de gitmez.</small></div></div>'
         + '<div class="bm-kural"><i class="fa fa-clone" style="color:#5C008E;font-size:18px;margin:2px 13px 0"></i>'
         + '<div><b>Birden fazla kanal açıksa hepsinden gider</b><small>Örneğin SMS ve WhatsApp ikisi de açıksa müşteri iki mesaj alır.</small></div></div>'
         + '</div><div class="bm-bilgi"><i class="fa fa-info-circle"></i><div>Şifre, oturum ve yetki gibi zorunlu sistem bildirimleri bu listede yer almaz ve kapatılamaz.</div></div></div></div>'
         + '</div>';
      h += '<div class="bm-not"><i class="fa fa-lock"></i> Bu ayarları yalnızca işletme sahibi değiştirebilir. Kaydedilen her değişiklik, kimin yaptığıyla birlikte Log Hareketleri\'ne yazılır.</div>';
      document.getElementById('bmIcerik').innerHTML = h;
      sayacGuncelle();
   }

   function yukle() {
      if (yuklendi) return;
      yuklendi = true;
      istek('GET', 'veri').then(function (j) { VERI = j; ORJ = kopya(j); ciz(); }).catch(function (e) {
         yuklendi = false;
         document.getElementById('bmIcerik').innerHTML = '<div class="bm-hata"><b><i class="fa fa-exclamation-triangle"></i> Ayarlar yüklenemedi</b><br>' + esc(e.message) + '</div>';
      });
   }

   kok.addEventListener('click', function (ev) {
      var c = ev.target.closest('.bm-cip');
      if (c && VERI) {
         var b = bildirimBul(VERI, c.getAttribute('data-kod')), a = c.getAttribute('data-alici'), k = c.getAttribute('data-kanal');
         b.alicilar[a][k] = +b.alicilar[a][k] ? 0 : 1; ciz();
      }
   });
   kok.addEventListener('change', function (ev) {
      var t = ev.target;
      if (!VERI) return;
      if (t.getAttribute('data-ana')) {
         var b = bildirimBul(VERI, t.getAttribute('data-kod')), a = t.getAttribute('data-alici'), r = b.alicilar[a];
         if (t.checked) {
            // Acilinca: once kaydedilmis halinde acik kanal varsa onlari geri getir, yoksa WhatsApp (bagliysa) ya da SMS
            var o = bildirimBul(ORJ, b.kod).alicilar[a], geri = +o.sms || +o.whatsapp || +o.push;
            if (geri) { r.sms = +o.sms; r.whatsapp = +o.whatsapp; r.push = +o.push; }
            else if (a !== 'musteri') r.push = 1; // personel/yonetici: ucretsiz uygulama bildirimi
            else if (VERI.durum && VERI.durum.wa_bagli) r.whatsapp = 1; else r.sms = 1;
         } else { r.sms = 0; r.whatsapp = 0; r.push = 0; }
         ciz();
      } else if (t.id === 'bmYedek') { VERI.wa_yedek_sms = t.checked; sayacGuncelle(); }
      else if (t.id === 'bmSaat') { VERI.hatirlatma_saat = +t.value; sayacGuncelle(); }
   });
   document.getElementById('bmKaydet').addEventListener('click', function () {
      if (!VERI) return;
      var l = farklar(), d = digerFark(), btn = this;
      if (!l.length && !Object.keys(d).length) { basariPenceresi('Ayarlarınız kayıtlı ve güncel.'); return; }
      if (btn.classList.contains('bekle')) return;
      btn.classList.add('bekle'); document.getElementById('bmKaydetYazi').textContent = 'Kaydediliyor…';
      var govde = { degisiklikler: JSON.stringify(l) };
      Object.keys(d).forEach(function (k) { govde[k] = d[k]; });
      istek('POST', 'kaydet', govde).then(function (j) {
         var t = new Date(); sonKayit = ('0' + t.getHours()).slice(-2) + ':' + ('0' + t.getMinutes()).slice(-2);
         btn.classList.remove('bekle');
         VERI = j; ORJ = kopya(j); ciz();
         basariPenceresi((l.length + Object.keys(d).length) + ' değişiklik ' + sonKayit + ' itibarıyla geçerli. Değişiklikler Log Hareketleri\'ne yazıldı.');
      }).catch(function (e) { btn.classList.remove('bekle'); sayacGuncelle(); toast(e.message, true); });
   });
   // Ekranin ortasinda basari penceresi (body'ye eklenir; Tamam / arka plan / Esc ile kapanir)
   function basariPenceresi(alt) {
      var eski = document.getElementById('bmModal'); if (eski) eski.remove();
      var m = document.createElement('div'); m.className = 'bm-modal-arka'; m.id = 'bmModal';
      m.innerHTML = '<div class="bm-modal" role="dialog" aria-modal="true"><div class="bm-modal-ikon"><i class="fa fa-check"></i></div>'
         + '<h3>Değişiklikleriniz başarıyla kaydedilmiştir</h3><p>' + esc(alt) + '</p><button type="button">Tamam</button></div>';
      function kapat() { m.remove(); document.removeEventListener('keydown', tus); }
      function tus(e) { if (e.key === 'Escape' || e.key === 'Enter') kapat(); }
      m.addEventListener('click', function (e) { if (e.target === m || e.target.tagName === 'BUTTON') kapat(); });
      document.addEventListener('keydown', tus);
      document.body.appendChild(m);
      m.querySelector('button').focus();
   }
   window.addEventListener('beforeunload', function (e) {
      if (VERI && (farklar().length || Object.keys(digerFark()).length)) { e.preventDefault(); e.returnValue = ''; }
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
