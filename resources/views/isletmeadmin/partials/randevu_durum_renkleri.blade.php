{{-- Randevu durum renkleri (RandevuDurumRenk). Yalniz DEGISTIRILEN durumlar gizli alana yazilir. --}}
@php
   $_drAyar = \App\Services\RandevuDurumRenk::harita($isletme->id);
   $_drVar = \App\Services\RandevuDurumRenk::VARSAYILAN;
   $_drNot = ['islemde' => 'Varsayılanda takvimde renk almaz', 'iptal' => 'İptal edilen hizmet'];
@endphp
<style>
.drr-satir{ display:flex; align-items:center; gap:8px; flex-wrap:wrap; padding:8px; border:1px solid #eee; border-radius:8px; margin-bottom:6px; background:#fff; max-width:760px; }
.drr-ad{ width:150px; font-weight:600; line-height:1.25; }
.drr-ad small{ display:block; font-weight:400; color:#6c757d; font-size:11px; }
.drr-satir input[type=color]{ width:34px; height:32px; padding:2px; border:1px solid #ccc; border-radius:6px; background:#fff; cursor:pointer; }
.drr-satir input[type=text]{ width:86px; height:32px; font-family:monospace; text-transform:uppercase; border:1px solid #ccc; border-radius:6px; padding:0 6px; }
.drr-on{ flex:1; min-width:110px; text-align:center; padding:5px 8px; border-radius:6px; font-weight:600; font-size:13px; }
.drr-durum{ font-size:11px; width:74px; text-align:center; border-radius:12px; padding:2px 6px; background:#eef0f5; color:#5b6172; }
.drr-durum.ozel{ background:#5C008E; color:#fff; }
.drr-sifirla{ padding:3px 9px; }
#drr_uyari{ display:none; max-width:760px; background:#fff7e0; border:1px solid #f1d48a; color:#7a5400; border-radius:8px; padding:8px 12px; font-size:13px; }
</style>
<div class="col-md-12">
   <div class="form-group" id="drr">
      <label class="d-block" style="font-weight:600;">Randevu Durum Renkleri</label>
      <small class="text-muted d-block mb-10">Randevu takvimi (web ve mobil) ile ön görüşme takviminde durumların rengi. Değiştirmediğiniz durumlar varsayılan rengiyle kalır. Durum rengi, tek renk ve renk sırası ayarının önündedir.</small>
      <input type="hidden" name="randevu_durum_renkleri" id="randevu_durum_renkleri" value="{{ json_encode((object) $_drAyar) }}">
      <div class="d-flex text-muted mb-5" style="font-size:11px;gap:8px;padding:0 8px;max-width:760px;">
         <span style="width:150px;">Durum</span><span style="width:128px;">Zemin rengi</span><span style="width:128px;">Yazı rengi</span><span>Önizleme</span>
      </div>
      @foreach(\App\Services\RandevuDurumRenk::ADLAR as $_d => $_ad)
      <div class="drr-satir" data-durum="{{ $_d }}" data-varsayilan="{{ strtoupper($_drVar[$_d] ?? '') }}">
         <span class="drr-ad">{{ $_ad }}@if(isset($_drNot[$_d]))<small>{{ $_drNot[$_d] }}</small>@endif</span>
         <input type="color" class="drr-bg" aria-label="{{ $_ad }} zemin rengi"><input type="text" class="drr-bg-hex" maxlength="7" aria-label="{{ $_ad }} zemin renk kodu">
         <input type="color" class="drr-yazi" aria-label="{{ $_ad }} yazı rengi"><input type="text" class="drr-yazi-hex" maxlength="7" aria-label="{{ $_ad }} yazı renk kodu">
         <span class="drr-on">{{ $_ad }}</span>
         <span class="drr-durum">Varsayılan</span>
         <button type="button" class="btn btn-sm btn-outline-secondary drr-sifirla" title="Varsayılan renge dön"><i class="fa fa-undo"></i> Varsayılan</button>
      </div>
      @endforeach
      <div id="drr_uyari" role="status"></div>
   </div>
</div>
<script>
(function(){
   var kok = document.getElementById('drr'), gizli = document.getElementById('randevu_durum_renkleri');
   if (!kok || !gizli) return;
   var ayar = {}, YEDEK = '#0891B2'; // islemde: secici acilis rengi
   try { ayar = JSON.parse(gizli.value) || {}; } catch(e){}
   function norm(v){ v = (v || '').trim().toUpperCase(); return v && v[0] !== '#' ? '#'+v : v; }
   function gecerli(v){ return /^#[0-9A-F]{6}$/.test(v); }
   function rgb(v){ return [1,3,5].map(function(i){ return parseInt(v.substr(i,2),16); }); }
   function otoYazi(bg){ var c = rgb(bg); return (0.299*c[0]+0.587*c[1]+0.114*c[2])/255 > 0.6 ? '#000000' : '#FFFFFF'; }
   function satirlar(){ return [].slice.call(kok.querySelectorAll('.drr-satir')); }
   function etkin(s){ // ekranda gorunecek renk; null => bu durum takvimde renksiz
      var a = ayar[s.getAttribute('data-durum')], v = s.getAttribute('data-varsayilan');
      return a || (v ? { bg:v, text:'#FFFFFF' } : null);
   }
   function ciz(s){
      var a = ayar[s.getAttribute('data-durum')], e = etkin(s), g = e || { bg:YEDEK, text:'#FFFFFF' };
      [['.drr-bg',g.bg],['.drr-bg-hex',e ? g.bg : ''],['.drr-yazi',g.text],['.drr-yazi-hex',e ? g.text : '']].forEach(function(x){
         var i = s.querySelector(x[0]); if (i !== document.activeElement || i.type === 'color') i.value = i.type === 'color' ? x[1].toLowerCase() : x[1];
      });
      var on = s.querySelector('.drr-on');
      on.style.background = e ? g.bg : '#fff'; on.style.color = e ? g.text : '#6c757d'; on.style.border = e ? '0' : '1px dashed #ccc';
      var d = s.querySelector('.drr-durum'); d.textContent = a ? 'Özel' : 'Varsayılan'; d.className = 'drr-durum'+(a ? ' ozel' : '');
      s.querySelector('.drr-sifirla').disabled = !a;
   }
   function yaz(){ gizli.value = JSON.stringify(ayar); uyari(); }
   // Yakin renk uyarisi: yalniz en az biri OZEL olan cift icin (kaydi engellemez)
   function uyari(){
      var l = satirlar(), m = [];
      l.forEach(function(x, i){ l.slice(i+1).forEach(function(y){
         var a = etkin(x), b = etkin(y);
         if (!a || !b || !(ayar[x.getAttribute('data-durum')] || ayar[y.getAttribute('data-durum')])) return;
         var c = rgb(a.bg), k = rgb(b.bg), u = Math.sqrt(Math.pow(c[0]-k[0],2)+Math.pow(c[1]-k[1],2)+Math.pow(c[2]-k[2],2));
         if (u < 60) m.push(x.querySelector('.drr-on').textContent+' ile '+y.querySelector('.drr-on').textContent);
      }); });
      var el = document.getElementById('drr_uyari');
      el.style.display = m.length ? 'block' : 'none';
      el.textContent = m.length ? 'Renkleri birbirine çok yakın: '+m.join(', ')+'. Takvimde ayırt etmek zor olabilir.' : '';
   }
   function degis(s, alan, v){
      v = norm(v); if (!gecerli(v)) return;
      var d = s.getAttribute('data-durum'), e = etkin(s) || { bg:YEDEK, text:'#FFFFFF' }, y = { bg:e.bg, text:e.text };
      if (alan === 'bg') { y.bg = v; y.text = otoYazi(v); } else y.text = v;
      if (y.bg === s.getAttribute('data-varsayilan') && y.text === '#FFFFFF') delete ayar[d]; else ayar[d] = y;
      ciz(s); yaz();
   }
   kok.addEventListener('input', function(ev){
      var s = ev.target.closest('.drr-satir'); if (!s) return;
      degis(s, /drr-bg/.test(ev.target.className) ? 'bg' : 'text', ev.target.value);
   });
   kok.addEventListener('change', function(ev){ var s = ev.target.closest('.drr-satir'); if (s) { if (document.activeElement && document.activeElement.blur && ev.target.type === 'text') ev.target.blur(); ciz(s); } });
   kok.addEventListener('click', function(ev){
      var b = ev.target.closest('.drr-sifirla'); if (!b) return;
      var s = b.closest('.drr-satir'); delete ayar[s.getAttribute('data-durum')]; ciz(s); yaz();
   });
   satirlar().forEach(ciz); uyari();
})();
</script>
