{{-- Sistem yoneticisi "Hesabina Gir" rozeti: yuzen + suruklenebilir, sayfayi ortmez.
     Konum tarayicida (localStorage) hatirlanir. Kisa dokunus = tiklama, surukleme = tasima. --}}
<div id="sysImpRozet" role="status" aria-live="polite">
   <span class="sir-tutamak" title="Sürükleyerek taşıyın" aria-hidden="true">
      <svg width="10" height="16" viewBox="0 0 10 16" fill="currentColor"><circle cx="2" cy="2" r="1.6"/><circle cx="8" cy="2" r="1.6"/><circle cx="2" cy="8" r="1.6"/><circle cx="8" cy="8" r="1.6"/><circle cx="2" cy="14" r="1.6"/><circle cx="8" cy="14" r="1.6"/></svg>
   </span>
   <span class="sir-nabiz" aria-hidden="true"></span>
   <span class="sir-metin">
      <strong>Sistem yöneticisi modu</strong>
      <small>Tüm işlemler loglanıyor</small>
   </span>
   <form method="post" action="/sistemyonetim/v2/impersonation-bitir" class="sir-form">
      @csrf
      <button type="submit" class="sir-buton">
         <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 14 4 9l5-5"/><path d="M4 9h11a5 5 0 0 1 0 10h-3"/></svg>
         Panele Dön
      </button>
   </form>
</div>
<style>
   #sysImpRozet{position:fixed;left:50%;bottom:22px;transform:translateX(-50%);z-index:99999;display:flex;align-items:center;gap:10px;
      padding:7px 7px 7px 10px;border-radius:999px;color:#fff;font-family:inherit;line-height:1.15;
      background:linear-gradient(135deg,rgba(217,154,31,.94),rgba(208,77,94,.94));
      -webkit-backdrop-filter:blur(8px);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.28);
      box-shadow:0 10px 30px rgba(208,77,94,.38),0 2px 6px rgba(0,0,0,.12);
      cursor:grab;user-select:none;-webkit-user-select:none;touch-action:none;
      transition:box-shadow .2s ease,opacity .2s ease;animation:sirGiris .45s cubic-bezier(.2,.9,.3,1.3)}
   #sysImpRozet.sir-konumlu{transform:none}
   #sysImpRozet.sir-suruklen{cursor:grabbing;box-shadow:0 18px 44px rgba(208,77,94,.5),0 4px 10px rgba(0,0,0,.18);opacity:.96;transition:none}
   #sysImpRozet .sir-tutamak{display:flex;opacity:.75}
   #sysImpRozet .sir-nabiz{position:relative;width:10px;height:10px;border-radius:50%;background:#fff;flex:0 0 auto}
   #sysImpRozet .sir-nabiz::after{content:"";position:absolute;inset:0;border-radius:50%;background:#fff;animation:sirNabiz 1.8s ease-out infinite}
   #sysImpRozet .sir-metin{display:flex;flex-direction:column;white-space:nowrap}
   #sysImpRozet .sir-metin strong{font-size:13px;font-weight:700}
   #sysImpRozet .sir-metin small{font-size:11px;font-weight:500;opacity:.88}
   #sysImpRozet .sir-form{margin:0}
   #sysImpRozet .sir-buton{display:flex;align-items:center;gap:6px;border:0;border-radius:999px;padding:8px 14px;cursor:pointer;
      background:#fff;color:#c2414f;font-size:13px;font-weight:700;font-family:inherit;white-space:nowrap;
      box-shadow:0 2px 8px rgba(0,0,0,.12);transition:transform .15s ease,box-shadow .15s ease}
   #sysImpRozet .sir-buton:hover{transform:translateY(-1px);box-shadow:0 6px 14px rgba(0,0,0,.18)}
   #sysImpRozet .sir-buton:active{transform:scale(.97)}
   @keyframes sirNabiz{0%{transform:scale(1);opacity:.7}100%{transform:scale(2.6);opacity:0}}
   @keyframes sirGiris{from{opacity:0;bottom:-30px}to{opacity:1;bottom:22px}}
   @media (max-width:520px){#sysImpRozet .sir-metin small{display:none}#sysImpRozet .sir-metin strong{font-size:12px}}
</style>
<script>
(function () {
   var el = document.getElementById('sysImpRozet');
   if (!el) return;
   var ANAHTAR = 'sysImpRozetKonum';

   function sinirla(x, y) {
      var w = el.offsetWidth, h = el.offsetHeight, k = 8;
      return {
         x: Math.min(Math.max(k, x), window.innerWidth - w - k),
         y: Math.min(Math.max(k, y), window.innerHeight - h - k)
      };
   }
   function yerlestir(x, y) {
      var p = sinirla(x, y);
      el.classList.add('sir-konumlu');
      el.style.animation = 'none';
      el.style.left = p.x + 'px';
      el.style.top = p.y + 'px';
      el.style.bottom = 'auto';
      return p;
   }

   try {
      var kayit = JSON.parse(localStorage.getItem(ANAHTAR) || 'null');
      if (kayit && typeof kayit.x === 'number') yerlestir(kayit.x, kayit.y);
   } catch (e) {}

   var basla = null, surukledi = false;
   el.addEventListener('pointerdown', function (e) {
      if (e.button !== 0) return;
      var r = el.getBoundingClientRect();
      basla = { px: e.clientX, py: e.clientY, ox: e.clientX - r.left, oy: e.clientY - r.top };
      surukledi = false;
   });
   window.addEventListener('pointermove', function (e) {
      if (!basla) return;
      if (!surukledi && Math.abs(e.clientX - basla.px) + Math.abs(e.clientY - basla.py) < 6) return;
      if (!surukledi) {
         surukledi = true;
         el.classList.add('sir-suruklen');
         try { el.setPointerCapture(e.pointerId); } catch (x) {}
      }
      yerlestir(e.clientX - basla.ox, e.clientY - basla.oy);
      e.preventDefault();
   });
   function birak() {
      if (!basla) return;
      basla = null;
      if (!surukledi) return;
      el.classList.remove('sir-suruklen');
      try {
         localStorage.setItem(ANAHTAR, JSON.stringify({ x: parseFloat(el.style.left), y: parseFloat(el.style.top) }));
      } catch (e) {}
   }
   window.addEventListener('pointerup', birak);
   window.addEventListener('pointercancel', birak);
   // Surukleme bitince alttaki "Panele Don" butonu yanlislikla tiklanmasin
   el.addEventListener('click', function (e) {
      if (surukledi) { e.preventDefault(); e.stopPropagation(); surukledi = false; }
   }, true);
   // Pencere kuculunce ekran disinda kalmasin
   window.addEventListener('resize', function () {
      if (el.classList.contains('sir-konumlu')) yerlestir(parseFloat(el.style.left), parseFloat(el.style.top));
   });
})();
</script>
