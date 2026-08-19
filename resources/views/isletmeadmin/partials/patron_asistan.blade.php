{{-- ============================================================
     PATRON ASISTANI — sesli/yazili serbest soru widget'i
     Cihazda STT (Web Speech API, bedava) + sunucuya sadece metin ->
     /isletmeyonetim/patron-asistan-sor -> dogal cevap + kart + TTS.
     Salt-okunur; mevcut dashboard'a additive. Detay: PatronAsistanServisi.
     ============================================================ --}}
<style>
  #pa-launcher{position:fixed;right:22px;bottom:22px;z-index:99998;width:60px;height:60px;border:none;border-radius:50%;
    background:linear-gradient(135deg,#5C008E,#7B2FB8 55%,#9D5DC8);color:#fff;font-size:26px;cursor:pointer;
    box-shadow:0 8px 24px rgba(92,0,142,.38);transition:transform .15s ease;}
  #pa-launcher:hover{transform:scale(1.06);}
  #pa-panel{position:fixed;right:22px;bottom:92px;z-index:99999;width:360px;max-width:calc(100vw - 32px);
    background:#fff;border-radius:16px;box-shadow:0 16px 50px rgba(30,27,50,.28);display:none;overflow:hidden;
    font-family:inherit;}
  #pa-panel.acik{display:block;}
  #pa-head{background:linear-gradient(135deg,#5C008E,#7B2FB8);color:#fff;padding:13px 16px;display:flex;
    align-items:center;justify-content:space-between;}
  #pa-head b{font-size:15px;font-weight:600;}
  #pa-head small{opacity:.85;font-size:11px;display:block;font-weight:400;}
  #pa-kapat{background:transparent;border:none;color:#fff;font-size:20px;cursor:pointer;line-height:1;}
  #pa-govde{padding:14px 16px;max-height:46vh;overflow-y:auto;background:#f7f6fb;}
  .pa-mesaj{margin-bottom:12px;}
  .pa-soru{text-align:right;}
  .pa-soru span{display:inline-block;background:#ece7f6;color:#3a2a5c;padding:8px 12px;border-radius:12px 12px 2px 12px;font-size:13px;max-width:85%;}
  .pa-cevap span{display:inline-block;background:#fff;border:1px solid #e7e2f0;color:#2a2340;padding:9px 13px;border-radius:12px 12px 12px 2px;font-size:13.5px;max-width:90%;box-shadow:0 2px 6px rgba(0,0,0,.03);}
  .pa-kart{margin-top:8px;background:#fff;border:1px solid #ece7f6;border-radius:12px;padding:10px 12px;}
  .pa-kart h6{margin:0 0 6px;font-size:12px;color:#7B2FB8;font-weight:700;text-transform:uppercase;letter-spacing:.3px;}
  .pa-satir{display:flex;justify-content:space-between;font-size:13px;padding:3px 0;border-bottom:1px dashed #f0edf6;}
  .pa-satir:last-child{border-bottom:none;}
  .pa-satir b{color:#5C008E;}
  #pa-alt{display:flex;gap:8px;align-items:center;padding:10px 12px;border-top:1px solid #eee;background:#fff;}
  #pa-metin{flex:1;border:1px solid #ddd;border-radius:20px;padding:9px 14px;font-size:13.5px;outline:none;}
  #pa-metin:focus{border-color:#9D5DC8;}
  .pa-btn{border:none;border-radius:50%;width:38px;height:38px;cursor:pointer;font-size:16px;flex:none;}
  #pa-mic{background:#f0ebf8;color:#5C008E;}
  #pa-mic.dinliyor{background:#e53935;color:#fff;animation:pa-pulse 1s infinite;}
  #pa-gonder{background:linear-gradient(135deg,#5C008E,#7B2FB8);color:#fff;}
  @keyframes pa-pulse{0%{box-shadow:0 0 0 0 rgba(229,57,53,.5);}70%{box-shadow:0 0 0 10px rgba(229,57,53,0);}100%{box-shadow:0 0 0 0 rgba(229,57,53,0);}}
  #pa-oneri{display:flex;flex-wrap:wrap;gap:6px;padding:0 12px 10px;background:#fff;}
  #pa-oneri button{background:#f4f1fb;border:1px solid #e7e2f0;color:#5c4a7a;border-radius:14px;padding:5px 10px;font-size:11.5px;cursor:pointer;}
  #pa-ses-toggle{font-size:11px;color:#8a7ba8;display:flex;align-items:center;gap:4px;cursor:pointer;user-select:none;}
</style>

<button id="pa-launcher" title="Patron Asistanı" onclick="paAc()">🎙️</button>

<div id="pa-panel" aria-live="polite">
  <div id="pa-head">
    <div>
      <b>Patron Asistanı</b>
      <small>Sesli ya da yazılı sor — “bugün kasa ne durumda?”</small>
    </div>
    <button id="pa-kapat" onclick="paKapat()" title="Kapat">×</button>
  </div>
  <div id="pa-govde">
    <div class="pa-mesaj pa-cevap"><span>Merhaba 👋 İşletmen hakkında ne öğrenmek istersin? Konuşmak için mikrofona bas ya da yaz.</span></div>
  </div>
  <div id="pa-oneri">
    <button onclick="paSor('Bugün kasa ne durumda?')">Bugün kasa</button>
    <button onclick="paSor('Bu ay ciro ne kadar?')">Bu ay ciro</button>
    <button onclick="paSor('Bu hafta en çok kim sattı?')">En çok kim sattı</button>
    <button onclick="paSor('Bugün kaç randevu var?')">Bugünkü randevular</button>
  </div>
  <div id="pa-alt">
    <button class="pa-btn" id="pa-mic" title="Konuş" onclick="paMic()">🎤</button>
    <input id="pa-metin" placeholder="Sorunu yaz veya söyle..." onkeydown="if(event.key==='Enter')paGonder()">
    <button class="pa-btn" id="pa-gonder" title="Gönder" onclick="paGonder()">➤</button>
  </div>
  <div style="padding:2px 12px 10px;background:#fff;">
    <label id="pa-ses-toggle"><input type="checkbox" id="pa-ses" checked> 🔊 Cevabı sesli oku</label>
  </div>
</div>

<script>
(function(){
  var URL_ASISTAN = "{{ url('/isletmeyonetim/patron-asistan-sor') }}";
  var CSRF = "{{ csrf_token() }}";
  var meslu = false; // istek suruyor mu

  // Mevcut sube parametresini koru (rapor fonksiyonlari dogru salonu bulsun)
  function subeParam(){
    try{ var u=new URLSearchParams(window.location.search); return u.get('sube')||''; }catch(e){ return ''; }
  }

  window.paAc = function(){ document.getElementById('pa-panel').classList.add('acik'); document.getElementById('pa-metin').focus(); };
  window.paKapat = function(){ document.getElementById('pa-panel').classList.remove('acik'); paDurdurSes(); };

  function govde(){ return document.getElementById('pa-govde'); }
  function kaydir(){ var g=govde(); g.scrollTop=g.scrollHeight; }

  function ekleSoru(t){
    var d=document.createElement('div'); d.className='pa-mesaj pa-soru';
    d.innerHTML='<span></span>'; d.querySelector('span').textContent=t; govde().appendChild(d); kaydir();
  }
  function ekleCevap(t){
    var d=document.createElement('div'); d.className='pa-mesaj pa-cevap';
    d.innerHTML='<span></span>'; d.querySelector('span').textContent=t; govde().appendChild(d); kaydir(); return d;
  }
  function ekleKart(k){
    if(!k) return;
    var tl=function(n){ try{return Number(n).toLocaleString('tr-TR')+' ₺';}catch(e){return n+' ₺';} };
    var h='<div class="pa-kart"><h6>'+(k.baslik||'')+'</h6>';
    if(typeof k.toplam!=='undefined') h+='<div class="pa-satir"><span>Toplam</span><b>'+tl(k.toplam)+'</b></div>';
    if(typeof k.toplam_gelir!=='undefined') h+='<div class="pa-satir"><span>Toplam tahsilat</span><b>'+tl(k.toplam_gelir)+'</b></div>';
    // Kasa/ozet satirlari
    if(k.satirlar && k.satirlar.length && k.tip==='kasa'){
      k.satirlar.forEach(function(s){ h+='<div class="pa-satir"><span>'+s.etiket+'</span><b>'+tl(s.tutar)+'</b></div>'; });
    }
    // Personel/hizmet/urun sirali listeler (ilk 5)
    if(k.satirlar && k.satirlar.length && (k.tip==='personel_sirali'||k.tip==='hizmet'||k.tip==='urun')){
      k.satirlar.slice(0,5).forEach(function(s){
        var ad = s.ad || s.personel_adi || s.hizmet_adi || s.urun_adi || '';
        var ciro = (typeof s.ciro!=='undefined')?s.ciro:0;
        h+='<div class="pa-satir"><span>'+ad+'</span><b>'+tl(ciro)+'</b></div>';
      });
    }
    if(k.tip==='musteri'){
      h+='<div class="pa-satir"><span>Aktif müşteri</span><b>'+(k.toplam_aktif||0)+'</b></div>';
      h+='<div class="pa-satir"><span>Yeni / Tekrar</span><b>'+(k.yeni||0)+' / '+(k.tekrar||0)+'</b></div>';
    }
    if(k.tip==='bugun' && k.liste){
      k.liste.slice(0,6).forEach(function(r){
        h+='<div class="pa-satir"><span>'+(r.saat||'')+' '+(r.musteri||'')+'</span><b>'+(r.personel||'')+'</b></div>';
      });
    }
    if(k.tip==='ozet'){
      h+='<div class="pa-satir"><span>Randevu / Adisyon</span><b>'+(k.toplam_randevu||0)+' / '+(k.toplam_adisyon||0)+'</b></div>';
    }
    h+='</div>';
    var d=document.createElement('div'); d.className='pa-mesaj pa-cevap'; d.innerHTML=h; govde().appendChild(d); kaydir();
  }

  // ---- TTS (cevabi sesli oku) ----
  function paDurdurSes(){ try{ window.speechSynthesis.cancel(); }catch(e){} }
  function seslendir(t){
    if(!document.getElementById('pa-ses').checked) return;
    if(!('speechSynthesis' in window)) return;
    paDurdurSes();
    var u=new SpeechSynthesisUtterance(t); u.lang='tr-TR'; u.rate=1.05;
    try{ window.speechSynthesis.speak(u); }catch(e){}
  }

  // ---- Sunucuya sor ----
  window.paSor = function(metin){
    document.getElementById('pa-panel').classList.add('acik');
    document.getElementById('pa-metin').value=metin; paGonder();
  };
  window.paGonder = function(){
    var inp=document.getElementById('pa-metin');
    var metin=(inp.value||'').trim();
    if(!metin || meslu) return;
    inp.value=''; ekleSoru(metin);
    var bekle=ekleCevap('…');
    meslu=true;
    var body='metin='+encodeURIComponent(metin)+'&sube='+encodeURIComponent(subeParam());
    fetch(URL_ASISTAN,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'},body:body,credentials:'same-origin'})
      .then(function(r){ return r.json().catch(function(){ return {basarili:false,cevap:'Cevap okunamadı.'}; }); })
      .then(function(j){
        bekle.querySelector('span').textContent = j.cevap || 'Bir sorun oldu.';
        if(j.kart) ekleKart(j.kart);
        if(j.seslendir && j.cevap) seslendir(j.cevap);
      })
      .catch(function(){ bekle.querySelector('span').textContent='Bağlantı hatası. Tekrar dener misin?'; })
      .then(function(){ meslu=false; kaydir(); });
  };

  // ---- STT (mikrofonla konus) ----
  var tanima=null, dinliyor=false;
  window.paMic = function(){
    var SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    if(!SR){ alert('Bu tarayıcı sesli girişi desteklemiyor. Lütfen yazarak sorun.'); return; }
    var mic=document.getElementById('pa-mic');
    if(dinliyor){ try{ tanima.stop(); }catch(e){} return; }
    tanima=new SR(); tanima.lang='tr-TR'; tanima.interimResults=false; tanima.maxAlternatives=1;
    tanima.onstart=function(){ dinliyor=true; mic.classList.add('dinliyor'); };
    tanima.onerror=function(){ dinliyor=false; mic.classList.remove('dinliyor'); };
    tanima.onend=function(){ dinliyor=false; mic.classList.remove('dinliyor'); };
    tanima.onresult=function(e){
      var t=e.results[0][0].transcript;
      document.getElementById('pa-metin').value=t;
      paGonder();
    };
    try{ tanima.start(); }catch(e){}
  };
})();
</script>
