<!DOCTYPE html>
<html lang="tr">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
   <title>{{ $sablon->ad }} — {{ $isletme->salon_adi ?? '' }}</title>
   <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
   <style>
      *,*::before,*::after { box-sizing: border-box; }
      body { background: linear-gradient(135deg, #f7f1fb 0%, #fdfbff 100%); font-family: 'Inter', -apple-system, sans-serif; margin:0; padding:0; min-height: 100vh; color: #2d1b3f; }
      .anket-kapsayici { max-width: 720px; margin: 24px auto; padding: 0 14px 60px; }
      .anket-baslik { text-align: center; padding: 28px 24px 22px; background: #5C008E; color:#fff; border-radius: 14px 14px 0 0; }
      .anket-baslik h2 { margin: 0; font-weight: 800; font-size: 22px; letter-spacing: -.4px; color:#fff; }
      .anket-baslik .alt { margin: 6px 0 0; opacity: .92; font-size: 13.5px; color: #fff; }
      .anket-kart { background: #fff; border-radius: 0 0 14px 14px; box-shadow: 0 18px 50px rgba(92,0,142,.10); padding: 24px 24px 28px; }
      .aciklama-kutusu { background: #faf5ff; border-left: 4px solid #5C008E; padding: 12px 16px; border-radius: 6px; margin-bottom: 22px; font-size: 13px; color: #4a3160; line-height: 1.55; }
      .soru-kutu { padding: 16px 18px; background: #fbfafd; border: 1px solid #ece6f3; border-radius: 10px; margin-bottom: 14px; }
      .soru-baslik { font-weight: 600; font-size: 14.5px; color: #2d1b3f; margin: 0 0 12px; line-height: 1.45; }
      .soru-baslik .zor { color: #e11d48; margin-left: 3px; }
      .bolum-baslik { background: linear-gradient(135deg,#5C008E,#7B2FB8); color:#fff; padding: 10px 16px; font-weight: 700; font-size: 13.5px; text-transform: uppercase; letter-spacing: .5px; border-radius: 8px; margin: 18px 0 10px; }
      .bilgi-kutu { background: #eef2ff; border:1px solid #c7d2fe; padding: 11px 15px; border-radius: 8px; font-size: 13px; color: #3730a3; margin-bottom: 14px; }

      /* NPS skala */
      .nps-skala { display:flex; gap:6px; flex-wrap:wrap; justify-content: space-between; }
      .nps-btn { flex: 1 1 8%; min-width: 36px; padding: 10px 0; text-align: center; font-weight: 700; font-size: 14px; background: #fff; border: 2px solid #ece6f3; border-radius: 8px; cursor: pointer; transition: all .15s; user-select: none; }
      .nps-btn:hover { border-color: #9D5DC8; }
      .nps-btn.secili { color: #fff; border-color: transparent; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(92,0,142,.18); }
      .nps-btn.detractor.secili { background: #ef4444; }
      .nps-btn.passive.secili   { background: #f59e0b; }
      .nps-btn.promoter.secili  { background: #10b981; }
      .nps-etiketler { display:flex; justify-content:space-between; font-size: 11px; color: #8a8295; margin-top: 8px; }

      /* Yıldız skala */
      .yildiz-grup { display: flex; gap: 8px; }
      .yildiz { font-size: 32px; cursor: pointer; color: #d8d2e0; transition: color .12s, transform .12s; user-select:none; }
      .yildiz:hover, .yildiz.aktif { color: #f59e0b; }
      .yildiz.aktif { transform: scale(1.05); }

      /* Evet-Hayır */
      .evet-hayir-grup { display: flex; gap: 12px; }
      .evet-hayir-btn { flex: 1; text-align: center; padding: 12px; border: 2px solid #ece6f3; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 14px; background:#fff; transition: all .15s; user-select:none; }
      .evet-hayir-btn:hover { border-color: #9D5DC8; }
      .evet-hayir-btn.evet-secili { border-color: #10b981; background: #ecfdf5; color: #047857; }
      .evet-hayir-btn.hayir-secili { border-color: #ef4444; background: #fef2f2; color: #b91c1c; }

      /* Tek seçim - radio chips */
      .secenek-grup { display: flex; flex-direction: column; gap: 8px; }
      .secenek-chip { padding: 11px 14px; border: 2px solid #ece6f3; border-radius: 8px; cursor: pointer; font-size: 13.5px; background: #fff; transition: all .15s; user-select:none; }
      .secenek-chip:hover { border-color: #9D5DC8; }
      .secenek-chip.aktif { background: #faf5ff; border-color: #5C008E; color: #3a1a52; font-weight: 600; }
      .secenek-chip .nokta { display:inline-block; width:14px; height:14px; border-radius:50%; border:2px solid #ccc; margin-right: 8px; vertical-align: middle; position: relative; }
      .secenek-chip.aktif .nokta { border-color: #5C008E; background: #5C008E; box-shadow: inset 0 0 0 3px #fff; }

      /* Çok seçim */
      .secenek-chip.cok-aktif { background: #faf5ff; border-color: #5C008E; color: #3a1a52; }
      .secenek-chip .kare { display:inline-block; width:14px; height:14px; border-radius:3px; border:2px solid #ccc; margin-right: 8px; vertical-align: middle; }
      .secenek-chip.cok-aktif .kare { border-color: #5C008E; background: #5C008E; }

      /* Inputs */
      .form-input, .form-area { width:100%; padding: 11px 14px; border: 2px solid #ece6f3; border-radius: 8px; font-size: 14px; font-family: inherit; background: #fff; color: #2d1b3f; transition: border-color .15s, box-shadow .15s; }
      .form-input:focus, .form-area:focus { outline: none; border-color: #5C008E; box-shadow: 0 0 0 3px rgba(92,0,142,.08); }
      .form-area { resize: vertical; min-height: 90px; line-height: 1.5; }

      .hata { display:none; color: #b91c1c; font-size: 12px; margin-top: 6px; font-weight: 500; }

      .kvkk-kutu { background: #fbfafd; border:1px solid #ece6f3; border-radius: 10px; padding: 14px 16px; margin: 16px 0 18px; }
      .kvkk-kutu label { display:flex; align-items:flex-start; gap:10px; cursor:pointer; margin:0; font-size:12.5px; color:#4a3160; line-height: 1.55; }
      .kvkk-kutu input[type=checkbox] { margin-top: 3px; transform: scale(1.15); accent-color: #5C008E; }

      .gonder-btn { width: 100%; padding: 15px; font-size: 15.5px; font-weight: 700; background: #5C008E; border: none; color: #fff; border-radius: 10px; cursor: pointer; transition: background .15s; letter-spacing: .2px; }
      .gonder-btn:hover { background: #48006e; }
      .gonder-btn:disabled { background: #b8a3cc; cursor: not-allowed; }

      .basarili-mesaj { text-align: center; padding: 50px 24px; }
      .basarili-mesaj .ikon { font-size: 64px; color: #10b981; margin-bottom: 12px; }
      .basarili-mesaj h3 { margin: 8px 0 6px; color: #155724; font-weight: 700; }
      .basarili-mesaj p { color: #5b6770; font-size: 13.5px; margin: 6px 0; }

      @media (max-width: 480px) {
         .anket-baslik { padding: 22px 18px; border-radius: 0; }
         .anket-kart { border-radius: 0; padding: 20px 16px 24px; }
         .anket-kapsayici { padding: 0; margin: 0; }
         .nps-btn { font-size: 13px; min-width: 30px; }
         .yildiz { font-size: 30px; }
      }
   </style>
</head>
<body>

@if($zaten_dolduruldu)
<div class="anket-kapsayici">
   <div class="anket-baslik">
      <h2>{{ $isletme->salon_adi ?? '' }}</h2>
      <div class="alt">{{ $sablon->ad }}</div>
   </div>
   <div class="anket-kart">
      <div class="basarili-mesaj">
         <div class="ikon"><i class="fa fa-check-circle"></i></div>
         <h3>Anket Zaten Dolduruldu</h3>
         <p>Geri bildiriminiz için teşekkür ederiz. Görüşleriniz hizmet kalitemizi geliştirmemize yardımcı oluyor.</p>
      </div>
   </div>
</div>

@elseif($suresi_bitti)
<div class="anket-kapsayici">
   <div class="anket-baslik">
      <h2>{{ $isletme->salon_adi ?? '' }}</h2>
      <div class="alt">{{ $sablon->ad }}</div>
   </div>
   <div class="anket-kart">
      <div class="basarili-mesaj">
         <div class="ikon" style="color:#f59e0b;"><i class="fa fa-clock-o"></i></div>
         <h3>Anket Süresi Doldu</h3>
         <p>Bu anket linkinin geçerlilik süresi sona ermiş. Görüşleriniz için işletmeyle iletişime geçebilirsiniz.</p>
      </div>
   </div>
</div>

@else
<div class="anket-kapsayici" id="form_bolumu">
   <div class="anket-baslik">
      <h2>{{ $isletme->salon_adi ?? '' }}</h2>
      <div class="alt">{{ $sablon->ad }}</div>
   </div>
   <div class="anket-kart">
      @if(!empty($sablon->aciklama))
      <div class="aciklama-kutusu">{!! nl2br(e($sablon->aciklama)) !!}</div>
      @endif

      @if($gonderim->ad_soyad)
         <div style="font-size:13px; color:#6b5b80; margin-bottom:18px;">
            <i class="fa fa-user-circle-o" style="color:#5C008E; margin-right:5px;"></i>
            Sn. <b>{{ $gonderim->ad_soyad }}</b>, geri bildiriminiz bizim için çok değerli.
         </div>
      @endif

      <div id="sorular_bolumu">
         @foreach($sorular as $idx => $soru)
            @php $tip = $soru['tip'] ?? 'metin'; $zorunlu = !empty($soru['zorunlu']); @endphp

            @if($tip === 'bolum_basligi')
               <div class="bolum-baslik">{{ $soru['soru'] ?? '' }}</div>

            @elseif($tip === 'bilgi_metni')
               <div class="bilgi-kutu">{!! nl2br(e($soru['soru'] ?? '')) !!}</div>

            @elseif($tip === 'nps')
               <div class="soru-kutu">
                  <p class="soru-baslik">{{ $soru['soru'] ?? '' }} @if($zorunlu)<span class="zor">*</span>@endif</p>
                  <div class="nps-skala" id="nps_grup_{{ $idx }}">
                     @for($n=0; $n<=10; $n++)
                        @php
                           $klas = $n <= 6 ? 'detractor' : ($n <= 8 ? 'passive' : 'promoter');
                        @endphp
                        <div class="nps-btn {{ $klas }}" data-deger="{{ $n }}" onclick="npsSec({{ $idx }}, {{ $n }})">{{ $n }}</div>
                     @endfor
                  </div>
                  <div class="nps-etiketler"><span>Hiç olası değil</span><span>Kesinlikle tavsiye ederim</span></div>
                  <input type="hidden" id="cevap_{{ $idx }}" data-tip="nps" data-zorunlu="{{ $zorunlu ? 1 : 0 }}" value="">
                  <div class="hata" id="hata_{{ $idx }}">Bu soruyu cevaplamak zorunludur.</div>
               </div>

            @elseif($tip === 'csat_yildiz')
               <div class="soru-kutu">
                  <p class="soru-baslik">{{ $soru['soru'] ?? '' }} @if($zorunlu)<span class="zor">*</span>@endif</p>
                  <div class="yildiz-grup" id="yildiz_grup_{{ $idx }}">
                     @for($n=1; $n<=5; $n++)
                        <span class="yildiz" data-deger="{{ $n }}" onclick="yildizSec({{ $idx }}, {{ $n }})">★</span>
                     @endfor
                  </div>
                  <input type="hidden" id="cevap_{{ $idx }}" data-tip="csat_yildiz" data-zorunlu="{{ $zorunlu ? 1 : 0 }}" value="">
                  <div class="hata" id="hata_{{ $idx }}">Lütfen yıldız vererek değerlendirin.</div>
               </div>

            @elseif($tip === 'evet_hayir')
               <div class="soru-kutu">
                  <p class="soru-baslik">{{ $soru['soru'] ?? '' }} @if($zorunlu)<span class="zor">*</span>@endif</p>
                  <div class="evet-hayir-grup">
                     <div class="evet-hayir-btn" id="evet_{{ $idx }}" onclick="evHayirSec({{ $idx }}, 'evet')"><i class="fa fa-check"></i> Evet</div>
                     <div class="evet-hayir-btn" id="hayir_{{ $idx }}" onclick="evHayirSec({{ $idx }}, 'hayir')"><i class="fa fa-times"></i> Hayır</div>
                  </div>
                  <input type="hidden" id="cevap_{{ $idx }}" data-tip="evet_hayir" data-zorunlu="{{ $zorunlu ? 1 : 0 }}" value="">
                  <div class="hata" id="hata_{{ $idx }}">Bu soruyu cevaplamak zorunludur.</div>
               </div>

            @elseif($tip === 'tek_secim')
               <div class="soru-kutu">
                  <p class="soru-baslik">{{ $soru['soru'] ?? '' }} @if($zorunlu)<span class="zor">*</span>@endif</p>
                  <div class="secenek-grup" id="secenek_grup_{{ $idx }}">
                     @foreach(($soru['secenekler'] ?? []) as $sIdx => $secenek)
                        <div class="secenek-chip" onclick="tekSec({{ $idx }}, this, @json($secenek))">
                           <span class="nokta"></span>{{ $secenek }}
                        </div>
                     @endforeach
                  </div>
                  <input type="hidden" id="cevap_{{ $idx }}" data-tip="tek_secim" data-zorunlu="{{ $zorunlu ? 1 : 0 }}" value="">
                  <div class="hata" id="hata_{{ $idx }}">Lütfen bir seçenek işaretleyin.</div>
               </div>

            @elseif($tip === 'cok_secim')
               <div class="soru-kutu">
                  <p class="soru-baslik">{{ $soru['soru'] ?? '' }} @if($zorunlu)<span class="zor">*</span>@endif</p>
                  <div class="secenek-grup" id="secenek_grup_{{ $idx }}">
                     @foreach(($soru['secenekler'] ?? []) as $sIdx => $secenek)
                        <div class="secenek-chip" onclick="cokSec({{ $idx }}, this, @json($secenek))">
                           <span class="kare"></span>{{ $secenek }}
                        </div>
                     @endforeach
                  </div>
                  <input type="hidden" id="cevap_{{ $idx }}" data-tip="cok_secim" data-zorunlu="{{ $zorunlu ? 1 : 0 }}" value="">
                  <div class="hata" id="hata_{{ $idx }}">Lütfen en az bir seçenek işaretleyin.</div>
               </div>

            @elseif($tip === 'metin')
               <div class="soru-kutu">
                  <p class="soru-baslik">{{ $soru['soru'] ?? '' }} @if($zorunlu)<span class="zor">*</span>@endif</p>
                  <input type="text" class="form-input" id="cevap_{{ $idx }}" placeholder="Cevabınızı yazın..." data-tip="metin" data-zorunlu="{{ $zorunlu ? 1 : 0 }}">
                  <div class="hata" id="hata_{{ $idx }}">Bu alan zorunludur.</div>
               </div>

            @elseif($tip === 'uzun_metin')
               <div class="soru-kutu">
                  <p class="soru-baslik">{{ $soru['soru'] ?? '' }} @if($zorunlu)<span class="zor">*</span>@endif</p>
                  <textarea class="form-area" id="cevap_{{ $idx }}" rows="4" placeholder="Görüşlerinizi paylaşın..." data-tip="uzun_metin" data-zorunlu="{{ $zorunlu ? 1 : 0 }}"></textarea>
                  <div class="hata" id="hata_{{ $idx }}">Bu alan zorunludur.</div>
               </div>
            @endif
         @endforeach
      </div>

      <div class="kvkk-kutu">
         <label>
            <input type="checkbox" id="kvkk_onay" checked>
            <span>
               <b>KVKK</b> kapsamında, anket cevaplarımın <b>{{ $isletme->salon_adi ?? '' }}</b> tarafından hizmet kalitesini iyileştirme amacıyla işlenmesine onay veriyorum.
            </span>
         </label>
      </div>

      <button type="button" class="gonder-btn" id="gonder_btn" onclick="anketiGonder()">
         <i class="fa fa-paper-plane"></i> Anketi Gönder
      </button>
   </div>
</div>

<div class="anket-kapsayici" id="basarili_bolumu" style="display:none;">
   <div class="anket-baslik">
      <h2>{{ $isletme->salon_adi ?? '' }}</h2>
   </div>
   <div class="anket-kart">
      {{-- Yüksek puan: Google Review CTA --}}
      <div id="basarili_google" style="display:none; text-align:center; padding:40px 20px;">
         <div style="font-size:54px; margin-bottom:8px;">🌟</div>
         <h3 style="margin:8px 0 8px; color:#2d1b3f; font-weight:700; font-size:22px;">Teşekkürler!</h3>
         <p style="color:#5b6770; font-size:14px; margin:0 0 4px;">Görüşlerin bizim için çok değerli.</p>
         <p style="color:#3a1a52; font-size:13.5px; margin:18px 0 16px; line-height:1.55; max-width:420px; margin-left:auto; margin-right:auto;">
            Mahallendeki diğer kişilere de bu deneyimini anlatır mısın?<br>
            <b>Tek tıkla Google'da yorum bırakabilirsin.</b>
         </p>
         <a id="google_review_link" href="#" target="_blank" rel="noopener" onclick="googleTiklamaTrack()"
            style="display:inline-flex; align-items:center; gap:10px; background:linear-gradient(135deg,#4285F4 0%,#1A73E8 100%); color:#fff; padding:14px 26px; border-radius:30px; text-decoration:none; font-weight:700; font-size:15px; box-shadow:0 8px 22px rgba(26,115,232,.32); transition:transform .15s;">
            <span style="background:#fff; color:#4285F4; width:26px; height:26px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-weight:900; font-size:14px;">G</span>
            Google'da Yorum Yaz
            <i class="fa fa-external-link" style="font-size:13px; opacity:.85;"></i>
         </a>
         <p style="color:#94a3b8; font-size:11.5px; margin:18px 0 0;">⏱️ Yaklaşık 30 saniye sürer · Yardımcı olacak</p>
      </div>

      {{-- Standart başarı mesajı --}}
      <div class="basarili-mesaj" id="basarili_standart">
         <div class="ikon"><i class="fa fa-heart"></i></div>
         <h3>Teşekkürler!</h3>
         <p>Geri bildiriminiz başarıyla iletildi.</p>
         <p style="font-size:12.5px; color:#8a8295;">Görüşleriniz hizmet kalitemizi geliştirmemize yardımcı olacak.</p>
      </div>
   </div>
</div>
@endif

<script>
(function(){
   var token = @json($gonderim->token ?? '');
   var soruSayisi = {{ count($sorular) }};
   var cokSecimDegerleri = {};

   window.npsSec = function(idx, deger){
      var grup = document.getElementById('nps_grup_'+idx);
      if(!grup) return;
      var btnler = grup.querySelectorAll('.nps-btn');
      btnler.forEach(function(b){ b.classList.remove('secili'); });
      var sec = grup.querySelector('[data-deger="'+deger+'"]');
      if(sec) sec.classList.add('secili');
      document.getElementById('cevap_'+idx).value = deger;
      var h = document.getElementById('hata_'+idx); if(h) h.style.display = 'none';
   };

   window.yildizSec = function(idx, deger){
      var grup = document.getElementById('yildiz_grup_'+idx);
      if(!grup) return;
      var yildizlar = grup.querySelectorAll('.yildiz');
      yildizlar.forEach(function(y, i){
         if(i < deger) y.classList.add('aktif'); else y.classList.remove('aktif');
      });
      document.getElementById('cevap_'+idx).value = deger;
      var h = document.getElementById('hata_'+idx); if(h) h.style.display = 'none';
   };

   window.evHayirSec = function(idx, deger){
      var e = document.getElementById('evet_'+idx);
      var h = document.getElementById('hayir_'+idx);
      if(e) e.classList.remove('evet-secili','hayir-secili');
      if(h) h.classList.remove('evet-secili','hayir-secili');
      if(deger === 'evet' && e) e.classList.add('evet-secili');
      if(deger === 'hayir' && h) h.classList.add('hayir-secili');
      document.getElementById('cevap_'+idx).value = deger;
      var ht = document.getElementById('hata_'+idx); if(ht) ht.style.display = 'none';
   };

   window.tekSec = function(idx, el, deger){
      var grup = document.getElementById('secenek_grup_'+idx);
      if(!grup) return;
      grup.querySelectorAll('.secenek-chip').forEach(function(c){ c.classList.remove('aktif'); });
      el.classList.add('aktif');
      document.getElementById('cevap_'+idx).value = deger;
      var h = document.getElementById('hata_'+idx); if(h) h.style.display = 'none';
   };

   window.cokSec = function(idx, el, deger){
      if(!cokSecimDegerleri[idx]) cokSecimDegerleri[idx] = [];
      var arr = cokSecimDegerleri[idx];
      var pos = arr.indexOf(deger);
      if(pos === -1){ arr.push(deger); el.classList.add('cok-aktif'); }
      else { arr.splice(pos, 1); el.classList.remove('cok-aktif'); }
      document.getElementById('cevap_'+idx).value = arr.join('||');
      var h = document.getElementById('hata_'+idx); if(h) h.style.display = 'none';
   };

   window.anketiGonder = function(){
      var hatalar = false;
      for(var i=0; i<soruSayisi; i++){
         var el = document.getElementById('cevap_'+i);
         if(!el) continue;
         var zorunlu = el.getAttribute('data-zorunlu') === '1';
         var deger = el.value;
         if(zorunlu && (deger === '' || deger === null)){
            var h = document.getElementById('hata_'+i); if(h) h.style.display = 'block';
            hatalar = true;
         }
      }
      if(hatalar){ window.scrollTo({top:0, behavior:'smooth'}); return; }

      var cevaplar = [];
      for(var j=0; j<soruSayisi; j++){
         var el = document.getElementById('cevap_'+j);
         if(!el) continue;
         var v = el.value;
         var tip = el.getAttribute('data-tip');
         if(tip === 'cok_secim' && v) v = v.split('||');
         cevaplar.push({ indeks: j, tip: tip, cevap: v });
      }

      var btn = document.getElementById('gonder_btn');
      btn.disabled = true;
      btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Gönderiliyor...';

      var fd = new FormData();
      fd.append('_token', '{{ csrf_token() }}');
      fd.append('token', token);
      fd.append('cevaplar_json', JSON.stringify(cevaplar));
      fd.append('kvkk_onay', document.getElementById('kvkk_onay').checked ? 1 : 0);

      fetch('/anket-kaydet', { method: 'POST', body: fd, credentials: 'same-origin' })
         .then(function(r){ return r.json(); })
         .then(function(resp){
            if(resp && resp.basarili){
               document.getElementById('form_bolumu').style.display = 'none';
               document.getElementById('basarili_bolumu').style.display = 'block';
               // Premium: yüksek puan ise Google Review CTA göster, değilse standart teşekkür
               if (resp.google_review_url) {
                  var link = document.getElementById('google_review_link');
                  if (link) link.href = resp.google_review_url;
                  document.getElementById('basarili_google').style.display = 'block';
                  document.getElementById('basarili_standart').style.display = 'none';
               } else {
                  document.getElementById('basarili_google').style.display = 'none';
                  document.getElementById('basarili_standart').style.display = 'block';
               }
               window.scrollTo({top:0, behavior:'smooth'});
            } else {
               alert(resp.mesaj || 'Bir hata oluştu.');
               btn.disabled = false;
               btn.innerHTML = '<i class="fa fa-paper-plane"></i> Anketi Gönder';
            }
         })
         .catch(function(){
            alert('Sunucu hatası. Lütfen tekrar deneyin.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-paper-plane"></i> Anketi Gönder';
         });
   };

   window.googleTiklamaTrack = function(){
      // Tracking — beklemeden tıklama analitiği gönder, link normal akıyor
      try {
         var fd = new FormData();
         fd.append('token', token);
         fetch('/anket-google-tiklandi', { method:'POST', body: fd, credentials:'same-origin', keepalive: true });
      } catch(e){}
   };
})();
</script>
</body>
</html>
